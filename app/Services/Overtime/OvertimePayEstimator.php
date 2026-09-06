<?php

namespace App\Services\Overtime;

use App\Services\Payroll\PayrollComparison;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use InvalidArgumentException;

class OvertimePayEstimator
{
    public function __construct(
        private OvertimeCalculator $overtime,
        private OvertimeRuleProvider $rules,
        private PayrollComparison $comparison,
    ) {}

    /**
     * Estimate gross overtime and the after-tax value of that overtime.
     *
     * Annualization: the entered hours are treated as repeating every pay
     * period for a full year (weekly × 52, biweekly × 26, and so on).
     *
     * @return array<string, mixed>
     */
    public function estimate(
        Province $province,
        Money $hourlyWage,
        float $regularHours,
        float $overtimeHours,
        PayFrequency $frequency,
        float $doubleHours = 0.0,
        bool $useContractPremium = false,
    ): array {
        if ($hourlyWage->isNegative() || $regularHours < 0 || $overtimeHours < 0 || $doubleHours < 0) {
            throw new InvalidArgumentException('Hours and wage cannot be negative.');
        }

        $gross = $this->overtime->gross(
            $province,
            $hourlyWage,
            $regularHours,
            $overtimeHours,
            $doubleHours,
            $useContractPremium,
        );

        $periods = $frequency->periods();
        $annualWithout = $gross['regular_pay']->multiply((string) $periods);
        $annualWith = $gross['total_gross']->multiply((string) $periods);

        $comparison = $this->comparison->compare(
            $province,
            $annualWithout,
            $annualWith,
            $frequency,
        );

        $periodNetWith = $comparison['modified']->metrics['net_annual']->divideBy($periods);
        $periodNetWithout = $comparison['baseline']->metrics['net_annual']->divideBy($periods);
        $afterTaxOvertime = $periodNetWith->subtract($periodNetWithout);
        $periodDeductions = $gross['total_gross']->subtract($periodNetWith);

        return [
            'rules' => $gross['rules'],
            'retrieved_date' => $this->rules->retrievedDate(),
            'disclaimer' => $this->rules->disclaimer(),
            'frequency' => $frequency,
            'periods' => $periods,
            'hourly_wage' => $hourlyWage,
            'overtime_rate' => $gross['overtime_rate'],
            'double_rate' => $gross['double_rate'],
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'double_hours' => $doubleHours,
            'regular_pay' => $gross['regular_pay'],
            'overtime_pay' => $gross['overtime_pay']->add($gross['double_pay']),
            'double_pay' => $gross['double_pay'],
            'total_gross' => $gross['total_gross'],
            'period_deductions' => $periodDeductions,
            'period_net' => $periodNetWith,
            'after_tax_overtime' => $afterTaxOvertime,
            'annual_without' => $annualWithout,
            'annual_with' => $annualWith,
            'comparison' => $comparison,
        ];
    }
}
