<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Payroll\PayrollComparison;
use App\Support\Money;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class BonusTaxCalculator extends Component
{
    public string $province = 'ON';

    public string $salary = '80000';

    public string $bonus = '10000';

    public string $rrsp = '';

    public bool $showAdvanced = false;

    public ?array $resultPayload = null;

    public ?string $errorMessage = null;

    public bool $hasCalculated = false;

    public function mount(?string $province = null, bool $autoCalculate = false): void
    {
        if ($province) {
            $resolved = Province::fromCode($province) ?? Province::fromSlug($province);

            if ($resolved) {
                $this->province = $resolved->value;
            }
        }

        if ($autoCalculate) {
            $this->calculate();
        }
    }

    public function updatedProvince(): void
    {
        app(Analytics::class)->record('province_selected', [
            'tool_key' => 'bonus',
            'province' => $this->province,
            'path' => request()->path(),
        ]);
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('bonus_calculator_started', [
            'tool_key' => 'bonus',
            'path' => request()->path(),
        ]);

        $this->validate([
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'salary' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'bonus' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'rrsp' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $province = Province::fromCode($this->province);

        try {
            $salary = Money::fromDollars($this->salary);
            $bonus = Money::fromDollars($this->bonus);
            $comparison = app(PayrollComparison::class)->compare(
                $province,
                $salary,
                $salary->add($bonus),
                sharedInputs: [
                    'rrsp' => $this->rrsp !== '' ? $this->rrsp : 0,
                ],
            );

            $this->resultPayload = $this->serialize($comparison, $salary, $bonus, $province);

            app(Analytics::class)->record('bonus_calculator_completed', [
                'tool_key' => 'bonus',
                'province' => $this->province,
                'annual_salary_cents' => $salary->add($bonus)->cents,
                'path' => request()->path(),
            ]);
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    /**
     * @param  array<string, mixed>  $comparison
     * @return array<string, mixed>
     */
    private function serialize(array $comparison, Money $salary, Money $bonus, Province $province): array
    {
        $pensionLabel = $province->usesQpp() ? 'QPP / QPP2' : 'CPP / CPP2';
        $insuranceLabel = $province->usesQpp() ? 'EI + QPIP' : 'EI';

        return [
            'gross_bonus' => $bonus->format(),
            'tax_increase' => $comparison['tax_delta']->format(),
            'pension_impact' => $comparison['pension_delta']->format(),
            'insurance_impact' => $comparison['insurance_delta']->format(),
            'net_bonus' => $comparison['net_annual_delta']->format(),
            'keep_percent' => $comparison['keep_percent'],
            'deduction_rate' => $comparison['incremental_deduction_rate'],
            'income_before' => $salary->format(),
            'income_after' => $salary->add($bonus)->format(),
            'pension_label' => $pensionLabel,
            'insurance_label' => $insuranceLabel,
            'federal_delta' => $comparison['federal_delta']->format(),
            'provincial_delta' => $comparison['provincial_delta']->format(),
        ];
    }

    public function render()
    {
        return view('livewire.bonus-tax-calculator', [
            'provinces' => Province::all(),
        ]);
    }
}
