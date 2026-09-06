<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Military\MilitaryPayRateProvider;
use App\Services\Military\MilitarySalaryCalculator as Calculator;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class MilitarySalaryCalculator extends Component
{
    public string $component = 'regular';

    public string $rank = 'private';

    public string $payLevel = 'standard';

    public string $increment = '1';

    public string $province = 'ON';

    public string $frequency = 'semimonthly';

    public string $reserveDays = '37';

    public string $taxableAllowances = '';

    public string $pensionOverride = '';

    public bool $showAdvanced = false;

    public ?array $resultPayload = null;

    public ?string $errorMessage = null;

    public bool $hasCalculated = false;

    public function mount(?string $province = null): void
    {
        if ($province) {
            $resolved = Province::fromCode($province) ?? Province::fromSlug($province);

            if ($resolved) {
                $this->province = $resolved->value;
            }
        }

        $this->syncPaySelection();
    }

    public function updatedComponent(): void
    {
        $this->rank = 'private';
        $this->syncPaySelection();
    }

    public function updatedRank(): void
    {
        $this->syncPaySelection();
    }

    public function updatedPayLevel(): void
    {
        $increments = array_keys($this->provider()->increments($this->component, $this->rank, $this->payLevel));
        $this->increment = $increments[0];
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('military_salary_calculator_started', [
            'tool_key' => 'military',
            'path' => request()->path(),
        ]);

        $this->validate([
            'component' => ['required', Rule::in(['regular', 'reserve'])],
            'rank' => ['required', 'string'],
            'payLevel' => ['required', 'string'],
            'increment' => ['required', 'string'],
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'frequency' => ['required', Rule::in(array_map(fn (PayFrequency $frequency) => $frequency->value, PayFrequency::supported()))],
            'reserveDays' => [$this->component === 'reserve' ? 'required' : 'nullable', 'integer', 'min:0', 'max:366'],
            'taxableAllowances' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'pensionOverride' => ['nullable', 'numeric', 'min:0', 'max:200000'],
        ]);

        $province = Province::fromCode($this->province);

        try {
            $result = app(Calculator::class)->calculate(
                $this->component,
                $this->rank,
                $this->increment,
                $province,
                PayFrequency::from($this->frequency),
                $this->payLevel,
                $this->reserveDays !== '' ? (int) $this->reserveDays : 37,
                $this->taxableAllowances !== '' ? Money::fromDollars($this->taxableAllowances) : null,
                $this->pensionOverride !== '' ? Money::fromDollars($this->pensionOverride) : null,
            );

            $this->resultPayload = $this->serialize($result);

            app(Analytics::class)->record('military_salary_calculator_completed', [
                'tool_key' => 'military',
                'province' => $this->province,
                'path' => request()->path(),
                'component' => $this->component,
                'rank' => $this->rank,
            ]);
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function serialize(array $result): array
    {
        $payroll = $result['payroll'];
        $pensionLabel = $result['payroll']->inputs['province'] === 'QC' ? 'QPP / QPP2' : 'CPP / CPP2';
        $insuranceLabel = $result['payroll']->inputs['province'] === 'QC' ? 'EI + QPIP' : 'EI';
        $pensionAmount = $result['pension']['amount'];

        return [
            'rank' => $result['pay']['rank_name'],
            'increment' => $result['pay']['increment_label'],
            'level' => $result['pay']['level'] === 'standard' ? null : 'Pay level '.$result['pay']['level'],
            'component' => $result['component_label'],
            'rate' => $result['pay']['rate']->format(),
            'unit' => $result['pay']['unit'],
            'reserve_days' => $result['reserve_days'],
            'base_annual' => $result['base_annual']->format(),
            'allowances' => $result['allowances']->format(),
            'gross_annual' => $result['gross_annual']->format(),
            'gross_monthly' => $result['gross_monthly']->format(),
            'gross_biweekly' => $result['gross_biweekly']->format(),
            'federal_tax' => $payroll->metrics['federal_tax']->format(),
            'provincial_tax' => $payroll->metrics['provincial_tax']->format(),
            'pension_payroll' => $pensionLabel,
            'cpp' => $payroll->metrics['cpp']->add($payroll->metrics['cpp2'])->format(),
            'insurance_label' => $insuranceLabel,
            'ei' => $payroll->metrics['ei']->add($payroll->metrics['qpip'])->format(),
            'caf_pension' => $pensionAmount->format(),
            'caf_pension_included' => $result['pension']['included'],
            'caf_pension_note' => $result['pension']['note'],
            'net_annual' => $payroll->metrics['net_annual']->format(),
            'net_monthly' => $payroll->metrics['net_annual']->divideBy(12)->format(),
            'net_biweekly' => $payroll->metrics['net_annual']->divideBy(26)->format(),
            'frequency' => $payroll->headlinePeriod,
            'net_period' => $payroll->metrics['net_period']->format(),
        ];
    }

    private function syncPaySelection(): void
    {
        $levels = $this->provider()->payLevels($this->component, $this->rank);
        $this->payLevel = in_array($this->payLevel, $levels, true) ? $this->payLevel : $levels[0];

        $increments = array_keys($this->provider()->increments($this->component, $this->rank, $this->payLevel));
        $this->increment = in_array($this->increment, $increments, true) ? $this->increment : $increments[0];
    }

    private function provider(): MilitaryPayRateProvider
    {
        return app(MilitaryPayRateProvider::class);
    }

    public function render()
    {
        $provider = $this->provider();
        $levels = $provider->payLevels($this->component, $this->rank);

        return view('livewire.military-salary-calculator', [
            'provinces' => Province::all(),
            'frequencies' => PayFrequency::supported(),
            'ranks' => $provider->ranks($this->component),
            'levels' => $levels,
            'showLevels' => count($levels) > 1,
            'increments' => $provider->increments($this->component, $this->rank, $this->payLevel),
            'edition' => $provider->sources()['edition'],
        ]);
    }
}
