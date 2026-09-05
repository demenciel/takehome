<?php

namespace App\Livewire;

use App\Calculators\Payroll\PayrollCalculator;
use App\Calculators\Results\CalculatorResult;
use App\Services\Analytics\Analytics;
use App\Support\PayFrequency;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class PaycheckCalculator extends Component
{
    public string $inputMode = 'annual';

    public string $salary = '';

    public string $hourlyWage = '';

    public string $hoursPerWeek = '40';

    public string $province = '';

    public string $frequency = 'biweekly';

    public string $rrsp = '';

    public string $pension = '';

    public string $unionDues = '';

    public string $otherDeductions = '';

    public string $additionalTax = '';

    public bool $showAdvanced = false;

    public ?array $resultPayload = null;

    public ?string $errorMessage = null;

    public bool $hasCalculated = false;

    public function mount(?string $province = null, ?int $salary = null, ?string $frequency = null, ?string $inputMode = null, bool $autoCalculate = false): void
    {
        if ($province) {
            $resolved = Province::fromCode($province) ?? Province::fromSlug($province);

            if ($resolved) {
                $this->province = $resolved->value;
            }
        }

        if ($salary) {
            $this->salary = (string) $salary;
        }

        if ($frequency && PayFrequency::tryFrom($frequency)) {
            $this->frequency = $frequency;
        }

        if ($inputMode && in_array($inputMode, ['annual', 'hourly'], true)) {
            $this->inputMode = $inputMode;
        }

        if ($autoCalculate && $this->province !== '' && ($this->salary !== '' || $this->hourlyWage !== '')) {
            $this->calculate();
        }
    }

    public function updatedProvince(): void
    {
        app(Analytics::class)->record('province_selected', [
            'tool_key' => 'paycheck',
            'province' => $this->province,
            'path' => request()->path(),
        ]);
    }

    public function updatedFrequency(): void
    {
        app(Analytics::class)->record('pay_frequency_selected', [
            'tool_key' => 'paycheck',
            'frequency' => $this->frequency,
            'path' => request()->path(),
        ]);
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('calculator_started', [
            'tool_key' => 'paycheck',
            'path' => request()->path(),
        ]);

        $this->validate($this->rules(), $this->messages());

        try {
            $result = app(PayrollCalculator::class)->calculate($this->calculatorInputs());
            $this->resultPayload = $this->serialize($result);

            app(Analytics::class)->record('calculator_completed', [
                'tool_key' => 'paycheck',
                'province' => $this->province,
                'frequency' => $this->frequency,
                'annual_salary_cents' => $result->inputs['annual_salary']->cents,
                'path' => request()->path(),
            ]);

            app(Analytics::class)->record('result_viewed', [
                'tool_key' => 'paycheck',
                'province' => $this->province,
                'frequency' => $this->frequency,
                'path' => request()->path(),
            ]);
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    public function markShared(): void
    {
        app(Analytics::class)->record('share_clicked', [
            'tool_key' => 'paycheck',
            'province' => $this->province ?: null,
            'frequency' => $this->frequency,
            'path' => request()->path(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'inputMode' => ['required', Rule::in(['annual', 'hourly'])],
            'salary' => [$this->inputMode === 'annual' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:10000000'],
            'hourlyWage' => [$this->inputMode === 'hourly' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:10000'],
            'hoursPerWeek' => [$this->inputMode === 'hourly' ? 'required' : 'nullable', 'numeric', 'min:1', 'max:80'],
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'frequency' => ['required', Rule::in(array_map(fn (PayFrequency $frequency) => $frequency->value, PayFrequency::supported()))],
            'rrsp' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'pension' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'unionDues' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'otherDeductions' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'additionalTax' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'salary.required' => __('calculator.error_salary'),
            'salary.numeric' => __('calculator.error_salary'),
            'hourlyWage.required' => __('calculator.error_hourly'),
            'hourlyWage.numeric' => __('calculator.error_hourly'),
            'hoursPerWeek.min' => __('calculator.error_hours'),
            'hoursPerWeek.max' => __('calculator.error_hours'),
            'province.required' => __('calculator.error_province'),
            'province.in' => __('calculator.error_province'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calculatorInputs(): array
    {
        return [
            'input_mode' => $this->inputMode,
            'annual_salary' => $this->salary !== '' ? $this->salary : 0,
            'hourly_wage' => $this->hourlyWage !== '' ? $this->hourlyWage : 0,
            'hours_per_week' => $this->hoursPerWeek !== '' ? $this->hoursPerWeek : 40,
            'province' => $this->province,
            'frequency' => $this->frequency,
            'rrsp' => $this->rrsp !== '' ? $this->rrsp : 0,
            'pension' => $this->pension !== '' ? $this->pension : 0,
            'union_dues' => $this->unionDues !== '' ? $this->unionDues : 0,
            'other_deductions' => $this->otherDeductions !== '' ? $this->otherDeductions : 0,
            'additional_tax' => $this->additionalTax !== '' ? $this->additionalTax : 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(CalculatorResult $result): array
    {
        $mapLines = fn (array $lines) => array_map(fn (array $line) => [
            'key' => $line['key'],
            'label' => $line['label'],
            'amount' => $line['amount']->format(),
            'kind' => $line['kind'],
        ], $lines);

        return [
            'summary' => $result->summary,
            'headline' => $result->headlineAmount->format(),
            'period' => $result->headlinePeriod,
            'period_breakdown' => $mapLines($result->periodBreakdown),
            'annual_breakdown' => $mapLines($result->annualBreakdown),
            'effective_tax_rate' => $result->metrics['effective_tax_rate'],
            'average_deductions' => $result->metrics['average_deductions']->format(),
            'share_text' => $result->shareText,
            'copy_text' => $result->copyText,
            'warnings' => $result->warnings,
            'tax_year' => $result->metadata['tax_year'],
        ];
    }

    public function render()
    {
        return view('livewire.paycheck-calculator', [
            'provinces' => Province::all(),
            'frequencies' => PayFrequency::supported(),
            'taxYear' => (int) config('tax.current_year'),
        ]);
    }
}
