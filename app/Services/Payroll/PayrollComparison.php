<?php

namespace App\Services\Payroll;

use App\Calculators\Payroll\PayrollCalculator;
use App\Calculators\Results\CalculatorResult;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;

class PayrollComparison
{
    public function __construct(private PayrollCalculator $payroll) {}

    /**
     * @param  array<string, mixed>  $sharedInputs
     * @return array{
     *     baseline: CalculatorResult,
     *     modified: CalculatorResult,
     *     gross_delta: Money,
     *     federal_delta: Money,
     *     provincial_delta: Money,
     *     tax_delta: Money,
     *     cpp_delta: Money,
     *     cpp2_delta: Money,
     *     ei_delta: Money,
     *     qpip_delta: Money,
     *     pension_delta: Money,
     *     insurance_delta: Money,
     *     net_annual_delta: Money,
     *     keep_percent: string,
     *     incremental_deduction_rate: string
     * }
     */
    public function compare(
        Province $province,
        Money $baselineAnnual,
        Money $modifiedAnnual,
        PayFrequency $frequency = PayFrequency::Annual,
        array $sharedInputs = [],
    ): array {
        $baseline = $this->payroll->calculate(array_merge($sharedInputs, [
            'annual_salary' => $baselineAnnual->dollars(),
            'province' => $province->value,
            'frequency' => $frequency->value,
        ]));

        $modified = $this->payroll->calculate(array_merge($sharedInputs, [
            'annual_salary' => $modifiedAnnual->dollars(),
            'province' => $province->value,
            'frequency' => $frequency->value,
        ]));

        $gross = $modified->inputs['annual_salary']->subtract($baseline->inputs['annual_salary']);
        $federal = $this->metricDelta($modified, $baseline, 'federal_tax');
        $provincial = $this->metricDelta($modified, $baseline, 'provincial_tax');
        $cpp = $this->metricDelta($modified, $baseline, 'cpp');
        $cpp2 = $this->metricDelta($modified, $baseline, 'cpp2');
        $ei = $this->metricDelta($modified, $baseline, 'ei');
        $qpip = $this->metricDelta($modified, $baseline, 'qpip');
        $net = $this->metricDelta($modified, $baseline, 'net_annual');
        $tax = $federal->add($provincial);
        $pension = $cpp->add($cpp2);
        $insurance = $ei->add($qpip);

        $keep = $gross->isZero()
            ? '0.0'
            : number_format(($net->cents / $gross->cents) * 100, 1);

        $deductionRate = $gross->isZero()
            ? '0.0'
            : number_format((($gross->cents - $net->cents) / $gross->cents) * 100, 1);

        return [
            'baseline' => $baseline,
            'modified' => $modified,
            'gross_delta' => $gross,
            'federal_delta' => $federal,
            'provincial_delta' => $provincial,
            'tax_delta' => $tax,
            'cpp_delta' => $cpp,
            'cpp2_delta' => $cpp2,
            'ei_delta' => $ei,
            'qpip_delta' => $qpip,
            'pension_delta' => $pension,
            'insurance_delta' => $insurance,
            'net_annual_delta' => $net,
            'keep_percent' => $keep,
            'incremental_deduction_rate' => $deductionRate,
        ];
    }

    private function metricDelta(CalculatorResult $modified, CalculatorResult $baseline, string $key): Money
    {
        return $modified->metrics[$key]->subtract($baseline->metrics[$key]);
    }
}
