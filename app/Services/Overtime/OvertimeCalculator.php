<?php

namespace App\Services\Overtime;

use App\Support\Money;
use App\Support\Province;
use InvalidArgumentException;

class OvertimeCalculator
{
    public function __construct(private OvertimeRuleProvider $rules) {}

    /**
     * @return array{
     *     rules: array<string, mixed>,
     *     overtime_rate: Money,
     *     double_rate: Money|null,
     *     regular_pay: Money,
     *     overtime_pay: Money,
     *     double_pay: Money,
     *     total_gross: Money,
     *     regular_hours: float,
     *     overtime_hours: float,
     *     double_hours: float
     * }
     */
    public function gross(
        Province $province,
        Money $hourlyWage,
        float $regularHours,
        float $overtimeHours,
        float $doubleHours = 0.0,
        bool $useContractPremium = false,
    ): array {
        if ($hourlyWage->isNegative() || $regularHours < 0 || $overtimeHours < 0 || $doubleHours < 0) {
            throw new InvalidArgumentException('Hours and wage cannot be negative.');
        }

        $rules = $this->rules->for($province);
        $overtimeRate = $this->overtimeRate($hourlyWage, $rules, $useContractPremium);
        $doubleRate = $rules['double_multiplier']
            ? $hourlyWage->multiply((string) $rules['double_multiplier'])
            : null;

        $regularPay = $this->hoursPay($hourlyWage, $regularHours);
        $overtimePay = $this->hoursPay($overtimeRate, $overtimeHours);
        $doublePay = $doubleRate ? $this->hoursPay($doubleRate, $doubleHours) : Money::zero();

        return [
            'rules' => $rules,
            'overtime_rate' => $overtimeRate,
            'double_rate' => $doubleRate,
            'regular_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'double_pay' => $doublePay,
            'total_gross' => $regularPay->add($overtimePay)->add($doublePay),
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'double_hours' => $doubleHours,
        ];
    }

    /**
     * Split a weekly total using the statutory weekly threshold.
     *
     * @return array{regular_hours: float, overtime_hours: float}
     */
    public function splitWeeklyHours(Province $province, float $totalHours): array
    {
        if ($totalHours < 0) {
            throw new InvalidArgumentException('Hours cannot be negative.');
        }

        $threshold = (float) $this->rules->for($province)['weekly_threshold'];
        $regular = min($totalHours, $threshold);
        $overtime = max(0.0, $totalHours - $threshold);

        return [
            'regular_hours' => $regular,
            'overtime_hours' => $overtime,
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    private function overtimeRate(Money $hourlyWage, array $rules, bool $useContractPremium = false): Money
    {
        $premium = $hourlyWage->multiply((string) $rules['multiplier']);

        if ($useContractPremium || ($rules['rate_basis'] ?? 'regular') !== 'greater_of_regular_or_min_ot') {
            return $premium;
        }

        $minimumWage = Money::fromDollars((string) ($rules['minimum_wage'] ?? '0'));
        $statutoryFloor = $minimumWage->multiply((string) $rules['multiplier']);

        return $hourlyWage->greaterThan($statutoryFloor) ? $hourlyWage : $statutoryFloor;
    }

    private function hoursPay(Money $rate, float $hours): Money
    {
        if ($hours <= 0) {
            return Money::zero();
        }

        return $rate->multiply(number_format($hours, 4, '.', ''));
    }
}
