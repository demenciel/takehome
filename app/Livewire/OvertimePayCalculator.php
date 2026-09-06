<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Overtime\OvertimeCalculator;
use App\Services\Overtime\OvertimePayEstimator;
use App\Services\Overtime\OvertimeRuleProvider;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class OvertimePayCalculator extends Component
{
    public string $province = 'ON';

    public string $hourlyWage = '30';

    public string $regularHours = '40';

    public string $overtimeHours = '8';

    public string $doubleHours = '';

    public string $frequency = 'weekly';

    public bool $useContractPremium = false;

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
            'tool_key' => 'overtime',
            'province' => $this->province,
            'path' => request()->path(),
        ]);
    }

    public function applyWeeklyThreshold(): void
    {
        $province = Province::fromCode($this->province);

        if (! $province || ! is_numeric($this->regularHours) || ! is_numeric($this->overtimeHours)) {
            return;
        }

        $total = (float) $this->regularHours + (float) $this->overtimeHours;
        $split = app(OvertimeCalculator::class)->splitWeeklyHours($province, $total);

        $this->regularHours = $this->formatHours($split['regular_hours']);
        $this->overtimeHours = $this->formatHours($split['overtime_hours']);
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('overtime_calculator_started', [
            'tool_key' => 'overtime',
            'path' => request()->path(),
        ]);

        $this->validate($this->rules());

        $province = Province::fromCode($this->province);

        try {
            $result = app(OvertimePayEstimator::class)->estimate(
                $province,
                Money::fromDollars($this->hourlyWage),
                (float) $this->regularHours,
                (float) $this->overtimeHours,
                PayFrequency::from($this->frequency),
                $this->doubleHours !== '' ? (float) $this->doubleHours : 0.0,
                $this->useContractPremium,
            );

            $this->resultPayload = $this->serialize($result);

            app(Analytics::class)->record('overtime_calculator_completed', [
                'tool_key' => 'overtime',
                'province' => $this->province,
                'frequency' => $this->frequency,
                'annual_salary_cents' => $result['annual_with']->cents,
                'path' => request()->path(),
            ]);
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'hourlyWage' => ['required', 'numeric', 'min:0', 'max:10000'],
            'regularHours' => ['required', 'numeric', 'min:0', 'max:168'],
            'overtimeHours' => ['required', 'numeric', 'min:0', 'max:168'],
            'doubleHours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'frequency' => ['required', Rule::in(array_map(fn (PayFrequency $frequency) => $frequency->value, PayFrequency::supported()))],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function serialize(array $result): array
    {
        $rules = $result['rules'];
        $frequency = $result['frequency'];

        return [
            'regular_pay' => $result['regular_pay']->format(),
            'overtime_pay' => $result['overtime_pay']->format(),
            'double_pay' => $result['double_pay']->format(),
            'total_gross' => $result['total_gross']->format(),
            'period_deductions' => $result['period_deductions']->format(),
            'period_net' => $result['period_net']->format(),
            'after_tax_overtime' => $result['after_tax_overtime']->format(),
            'overtime_rate' => $result['overtime_rate']->format(),
            'double_rate' => $result['double_rate']?->format(),
            'hourly_wage' => $result['hourly_wage']->format(),
            'overtime_hours' => $this->formatHours($result['overtime_hours']),
            'double_hours' => $this->formatHours($result['double_hours']),
            'regular_hours' => $this->formatHours($result['regular_hours']),
            'frequency_label' => $frequency->label(),
            'periods' => $result['periods'],
            'annual_without' => $result['annual_without']->format(),
            'annual_with' => $result['annual_with']->format(),
            'keep_percent' => $result['comparison']['keep_percent'],
            'weekly_threshold' => $rules['weekly_threshold'],
            'daily_threshold' => $rules['daily_threshold'],
            'multiplier' => $rules['multiplier'],
        ];
    }

    private function formatHours(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.') ?: '0';
    }

    public function render()
    {
        $province = Province::fromCode($this->province);
        $rules = $province ? app(OvertimeRuleProvider::class)->for($province) : null;

        return view('livewire.overtime-pay-calculator', [
            'provinces' => Province::all(),
            'frequencies' => PayFrequency::supported(),
            'rules' => $rules,
            'showsDoubleTime' => (bool) ($rules['daily_double_threshold'] ?? null),
            'showsContractPremium' => ($rules['rate_basis'] ?? 'regular') === 'greater_of_regular_or_min_ot',
        ]);
    }
}
