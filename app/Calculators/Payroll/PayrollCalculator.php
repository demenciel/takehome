<?php

namespace App\Calculators\Payroll;

use App\Calculators\Contracts\Calculator;
use App\Calculators\Results\CalculatorResult;
use App\Services\Tax\CppCalculator;
use App\Services\Tax\EiCalculator;
use App\Services\Tax\FederalTaxCalculator;
use App\Services\Tax\ProvincialTaxCalculator;
use App\Services\Tax\QpipCalculator;
use App\Services\Tax\QppCalculator;
use App\Services\Tax\TaxRuleProvider;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use InvalidArgumentException;

class PayrollCalculator implements Calculator
{
    public function __construct(
        private TaxRuleProvider $rules,
        private CppCalculator $cpp,
        private QppCalculator $qpp,
        private EiCalculator $ei,
        private QpipCalculator $qpip,
        private FederalTaxCalculator $federal,
        private ProvincialTaxCalculator $provincial,
    ) {}

    public function key(): string
    {
        return 'paycheck';
    }

    public function calculate(array $inputs): CalculatorResult
    {
        $normalized = $this->normalize($inputs);
        $year = $normalized['tax_year'];
        $rules = $this->rules->forYear($year);
        $province = $normalized['province'];
        $frequency = $normalized['frequency'];
        $salary = $normalized['annual_salary'];

        $pension = $province->usesQpp()
            ? $this->qpp->annual($salary, $rules->qpp)
            : $this->cpp->annual($salary, $rules->cpp);

        $ei = $this->ei->annual($salary, $province, $rules->ei);
        $qpip = $this->qpip->annual($salary, $province, $rules->qpip);

        $f5 = $pension['additional']->add($pension['second_additional']);
        $taxable = $salary
            ->subtract($normalized['rrsp'])
            ->subtract($normalized['pension'])
            ->subtract($normalized['union_dues'])
            ->subtract($f5);

        if ($taxable->isNegative()) {
            $taxable = Money::zero();
        }

        $provincialTaxable = $taxable;

        if ($province->usesQpp()) {
            $workerDeduction = Money::fromDollars($rules->province($province)['worker_deduction'] ?? '0');
            $provincialTaxable = $taxable->subtract($workerDeduction);

            if ($provincialTaxable->isNegative()) {
                $provincialTaxable = Money::zero();
            }
        }

        $federal = $this->federal->annual(
            $taxable,
            $salary,
            $pension['base'],
            $ei,
            $qpip,
            $province,
            $rules->federal,
        );

        $provincial = $this->provincial->annual(
            $provincialTaxable,
            $salary,
            $pension['base'],
            $ei,
            $qpip,
            $province,
            $rules->province($province),
            $rules->federal,
        );

        $incomeTax = $federal['t1']->add($provincial['t2']);
        $additionalTaxAnnual = $normalized['additional_tax_per_period']->multiply((string) $frequency->periods());
        $statutory = $pension['contribution']->add($pension['second_additional'])->add($ei)->add($qpip);
        $optional = $normalized['rrsp']
            ->add($normalized['pension'])
            ->add($normalized['union_dues'])
            ->add($normalized['other_deductions']);

        $totalDeductions = $incomeTax->add($statutory)->add($optional)->add($additionalTaxAnnual);
        $netAnnual = $salary->subtract($totalDeductions);

        if ($netAnnual->isNegative()) {
            $netAnnual = Money::zero();
        }

        $periods = $frequency->periods();
        $periodNet = $netAnnual->divideBy($periods);
        $periodGross = $salary->divideBy($periods);
        $periodTax = $incomeTax->add($additionalTaxAnnual)->divideBy($periods);
        $periodPension = $pension['contribution']->add($pension['second_additional'])->divideBy($periods);
        $periodEi = $ei->divideBy($periods);
        $periodQpip = $qpip->divideBy($periods);
        $periodOther = $optional->divideBy($periods);
        $periodDeductions = $periodGross->subtract($periodNet);

        $effectiveRate = $salary->isZero()
            ? '0.0'
            : number_format(($incomeTax->cents / $salary->cents) * 100, 1);

        $warnings = $this->warnings($province, $salary, $pension, $ei, $year);
        $pensionKey = $province->usesQpp() ? 'qpp' : 'cpp';
        $pensionLabel = $province->usesQpp() ? __('calculator.qpp') : __('calculator.cpp');

        $periodBreakdown = [
            ['key' => 'gross', 'label' => __('calculator.gross'), 'amount' => $periodGross, 'kind' => 'gross'],
            ['key' => 'income_tax', 'label' => __('calculator.income_tax'), 'amount' => $periodTax, 'kind' => 'deduction'],
            ['key' => $pensionKey, 'label' => $pensionLabel, 'amount' => $periodPension, 'kind' => 'deduction'],
            ['key' => 'ei', 'label' => __('calculator.ei'), 'amount' => $periodEi, 'kind' => 'deduction'],
        ];

        if ($periodQpip->isPositive()) {
            $periodBreakdown[] = ['key' => 'qpip', 'label' => __('calculator.qpip'), 'amount' => $periodQpip, 'kind' => 'deduction'];
        }

        if ($periodOther->isPositive()) {
            $periodBreakdown[] = ['key' => 'other', 'label' => __('calculator.other_deductions'), 'amount' => $periodOther, 'kind' => 'deduction'];
        }

        $annualBreakdown = [
            ['key' => 'gross', 'label' => __('calculator.gross_salary'), 'amount' => $salary, 'kind' => 'gross'],
            ['key' => 'income_tax', 'label' => __('calculator.estimated_income_tax'), 'amount' => $incomeTax, 'kind' => 'deduction'],
            ['key' => $pensionKey, 'label' => $province->usesQpp() ? __('calculator.estimated_qpp') : __('calculator.estimated_cpp'), 'amount' => $pension['contribution']->add($pension['second_additional']), 'kind' => 'deduction'],
            ['key' => 'ei', 'label' => __('calculator.estimated_ei'), 'amount' => $ei, 'kind' => 'deduction'],
        ];

        if ($qpip->isPositive()) {
            $annualBreakdown[] = ['key' => 'qpip', 'label' => __('calculator.estimated_qpip'), 'amount' => $qpip, 'kind' => 'deduction'];
        }

        if ($optional->isPositive()) {
            $annualBreakdown[] = ['key' => 'other', 'label' => __('calculator.other_deductions'), 'amount' => $optional, 'kind' => 'deduction'];
        }

        $annualBreakdown[] = ['key' => 'net', 'label' => __('calculator.estimated_take_home'), 'amount' => $netAnnual, 'kind' => 'net'];

        $shareText = __('calculator.share_text', [
            'salary' => $salary->format(),
            'province' => $province->name(),
            'net' => $periodNet->format(),
            'frequency' => $frequency->adverb(),
        ]);

        $copyText = implode("\n", array_filter([
            $salary->format().' salary',
            $province->name(),
            $frequency->label(),
            '',
            'Estimated take-home: '.$periodNet->format(),
            'Gross: '.$periodGross->format(),
            'Income tax: '.$periodTax->format(),
            $pensionLabel.': '.$periodPension->format(),
            'EI: '.$periodEi->format(),
            $periodQpip->isPositive() ? 'QPIP: '.$periodQpip->format() : null,
            $periodOther->isPositive() ? 'Other deductions: '.$periodOther->format() : null,
        ]));

        return new CalculatorResult(
            summary: __('calculator.result_heading'),
            headlineAmount: $periodNet,
            headlinePeriod: $frequency->adverb(),
            inputs: [
                'annual_salary' => $salary,
                'province' => $province->code(),
                'province_name' => $province->name(),
                'frequency' => $frequency->value,
                'tax_year' => $year,
                'rrsp' => $normalized['rrsp'],
                'pension' => $normalized['pension'],
                'union_dues' => $normalized['union_dues'],
                'other_deductions' => $normalized['other_deductions'],
                'additional_tax_per_period' => $normalized['additional_tax_per_period'],
            ],
            periodBreakdown: $periodBreakdown,
            annualBreakdown: $annualBreakdown,
            metrics: [
                'effective_tax_rate' => $effectiveRate,
                'average_deductions' => $periodDeductions,
                'federal_tax' => $federal['t1'],
                'provincial_tax' => $provincial['t2'],
                'taxable_income' => $taxable,
                'cpp' => $pension['contribution'],
                'cpp2' => $pension['second_additional'],
                'ei' => $ei,
                'qpip' => $qpip,
                'net_annual' => $netAnnual,
                'net_period' => $periodNet,
            ],
            warnings: $warnings,
            metadata: [
                'calculator' => $this->key(),
                'tax_year' => $year,
                'edition' => $rules->sources['edition'] ?? null,
                'claim_code' => 1,
                'federal_bpa' => $federal['bpa'],
                'provincial_bpa' => $provincial['bpa'],
            ],
            shareText: $shareText,
            copyText: $copyText,
        );
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @return array{
     *     annual_salary: Money,
     *     province: Province,
     *     frequency: PayFrequency,
     *     tax_year: int,
     *     rrsp: Money,
     *     pension: Money,
     *     union_dues: Money,
     *     other_deductions: Money,
     *     additional_tax_per_period: Money
     * }
     */
    private function normalize(array $inputs): array
    {
        $province = $inputs['province'] ?? null;

        if (is_string($province)) {
            $province = Province::fromCode($province) ?? Province::fromSlug($province);
        }

        if (! $province instanceof Province) {
            throw new InvalidArgumentException('A valid province is required.');
        }

        $frequency = $inputs['frequency'] ?? PayFrequency::Biweekly;

        if (is_string($frequency)) {
            $frequency = PayFrequency::tryFrom($frequency);
        }

        if (! $frequency instanceof PayFrequency) {
            throw new InvalidArgumentException('A valid pay frequency is required.');
        }

        $annual = $this->money($inputs['annual_salary'] ?? 0);

        if (isset($inputs['input_mode']) && $inputs['input_mode'] === 'hourly') {
            $hourly = $this->money($inputs['hourly_wage'] ?? 0);
            $hours = (float) ($inputs['hours_per_week'] ?? 40);

            if ($hourly->isZero() || $hours <= 0) {
                throw new InvalidArgumentException('Enter a valid hourly wage and hours per week.');
            }

            $weekly = Money::fromRateProduct($hourly->cents, (string) $hours);
            $annual = $weekly->multiply('52');
        }

        if ($annual->isNegative()) {
            throw new InvalidArgumentException('Salary cannot be negative.');
        }

        return [
            'annual_salary' => $annual,
            'province' => $province,
            'frequency' => $frequency,
            'tax_year' => (int) ($inputs['tax_year'] ?? $this->rules->currentYear()),
            'rrsp' => $this->money($inputs['rrsp'] ?? 0),
            'pension' => $this->money($inputs['pension'] ?? 0),
            'union_dues' => $this->money($inputs['union_dues'] ?? 0),
            'other_deductions' => $this->money($inputs['other_deductions'] ?? 0),
            'additional_tax_per_period' => $this->money($inputs['additional_tax'] ?? 0),
        ];
    }

    private function money(mixed $value): Money
    {
        if ($value instanceof Money) {
            return $value;
        }

        if ($value === null || $value === '') {
            return Money::zero();
        }

        if (is_int($value)) {
            return Money::fromDollars($value);
        }

        return Money::fromDollars((string) $value);
    }

    /**
     * @param  array{contribution: Money, second_additional: Money}  $pension
     * @return list<string>
     */
    private function warnings(Province $province, Money $salary, array $pension, Money $ei, int $year): array
    {
        $warnings = [
            __('calculator.disclaimer'),
        ];

        if ($province->usesQpp()) {
            $warnings[] = __('calculator.quebec_estimate_note');
        }

        if ($pension['second_additional']->isPositive()) {
            $warnings[] = __('calculator.cpp2_note');
        }

        return $warnings;
    }
}
