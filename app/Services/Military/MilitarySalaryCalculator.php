<?php

namespace App\Services\Military;

use App\Calculators\Payroll\PayrollCalculator;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use InvalidArgumentException;

class MilitarySalaryCalculator
{
    public function __construct(
        private MilitaryPayRateProvider $rates,
        private CafPensionCalculator $pension,
        private PayrollCalculator $payroll,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function calculate(
        string $component,
        string $rank,
        string $increment,
        Province $province,
        PayFrequency $frequency,
        ?string $payLevel = null,
        int $reserveDays = 37,
        ?Money $taxableAllowances = null,
        ?Money $pensionOverride = null,
    ): array {
        if ($reserveDays < 0) {
            throw new InvalidArgumentException('Paid reserve days cannot be negative.');
        }

        $pay = $this->rates->lookup($component, $rank, $increment, $payLevel);
        $allowances = $taxableAllowances ?? Money::zero();

        if ($allowances->isNegative()) {
            throw new InvalidArgumentException('Taxable allowances cannot be negative.');
        }

        $baseAnnual = $pay['unit'] === 'monthly'
            ? $pay['rate']->multiply('12')
            : $pay['rate']->multiply((string) $reserveDays);

        $grossAnnual = $baseAnnual->add($allowances);
        $pension = $this->pension->annual($component, $baseAnnual, $pensionOverride);

        $payroll = $this->payroll->calculate([
            'annual_salary' => $grossAnnual->dollars(),
            'province' => $province->value,
            'frequency' => $frequency->value,
            'pension' => $pension['amount']->dollars(),
        ]);

        $sources = $this->rates->sources();

        return [
            'component' => $component,
            'component_label' => $component === 'regular' ? 'Regular Force' : 'Reserve Force (Class A / B)',
            'pay' => $pay,
            'reserve_days' => $component === 'reserve' ? $reserveDays : null,
            'base_annual' => $baseAnnual,
            'allowances' => $allowances,
            'gross_annual' => $grossAnnual,
            'gross_monthly' => $grossAnnual->divideBy(12),
            'gross_biweekly' => $grossAnnual->divideBy(26),
            'pension' => $pension,
            'payroll' => $payroll,
            'sources' => $sources,
            'effective_date' => $sources['effective_date'],
            'retrieved_date' => $sources['retrieved_date'],
            'edition' => $sources['edition'],
        ];
    }
}
