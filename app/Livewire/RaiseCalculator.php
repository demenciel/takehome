<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Payroll\PayrollComparison;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class RaiseCalculator extends Component
{
    public string $province = 'ON';

    public string $currentSalary = '75000';

    public string $newSalary = '85000';

    public string $raisePercent = '';

    public string $inputMode = 'amount';

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
            'tool_key' => 'raise',
            'province' => $this->province,
            'path' => request()->path(),
        ]);
    }

    public function updatedRaisePercent(): void
    {
        if ($this->currentSalary === '' || ! is_numeric($this->currentSalary) || ! is_numeric($this->raisePercent)) {
            return;
        }

        $current = Money::fromDollars($this->currentSalary);
        $factor = bcadd('1', bcdiv((string) $this->raisePercent, '100', 8), 8);
        $this->newSalary = $current->multiply($factor)->dollars();
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('raise_calculator_started', [
            'tool_key' => 'raise',
            'path' => request()->path(),
        ]);

        if ($this->inputMode === 'percent') {
            $this->updatedRaisePercent();
        }

        $this->validate([
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'currentSalary' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'newSalary' => ['required', 'numeric', 'min:0', 'max:10000000', 'gte:currentSalary'],
            'raisePercent' => [$this->inputMode === 'percent' ? 'required' : 'nullable', 'numeric', 'min:0', 'max:500'],
        ], [
            'newSalary.gte' => 'The new salary cannot be lower than the current salary.',
        ]);

        $province = Province::fromCode($this->province);

        try {
            $current = Money::fromDollars($this->currentSalary);
            $new = Money::fromDollars($this->newSalary);
            $comparison = app(PayrollComparison::class)->compare($province, $current, $new);

            $this->resultPayload = $this->serialize($comparison, $current, $new, $province);

            app(Analytics::class)->record('raise_calculator_completed', [
                'tool_key' => 'raise',
                'province' => $this->province,
                'annual_salary_cents' => $new->cents,
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
    private function serialize(array $comparison, Money $current, Money $new, Province $province): array
    {
        $net = $comparison['net_annual_delta'];
        $pensionLabel = $province->usesQpp() ? 'QPP / QPP2' : 'CPP / CPP2';
        $insuranceLabel = $province->usesQpp() ? 'EI + QPIP' : 'EI';

        $row = fn (string $label, Money $before, Money $after) => [
            'label' => $label,
            'before' => $before->format(),
            'after' => $after->format(),
            'difference' => $after->subtract($before)->format(),
        ];

        $baseline = $comparison['baseline'];
        $modified = $comparison['modified'];
        $taxBefore = $baseline->metrics['federal_tax']->add($baseline->metrics['provincial_tax']);
        $taxAfter = $modified->metrics['federal_tax']->add($modified->metrics['provincial_tax']);
        $pensionBefore = $baseline->metrics['cpp']->add($baseline->metrics['cpp2']);
        $pensionAfter = $modified->metrics['cpp']->add($modified->metrics['cpp2']);
        $insuranceBefore = $baseline->metrics['ei']->add($baseline->metrics['qpip']);
        $insuranceAfter = $modified->metrics['ei']->add($modified->metrics['qpip']);

        return [
            'gross_raise' => $comparison['gross_delta']->format(),
            'net_raise' => $net->format(),
            'monthly' => $net->divideBy(PayFrequency::Monthly->periods())->format(),
            'biweekly' => $net->divideBy(PayFrequency::Biweekly->periods())->format(),
            'weekly' => $net->divideBy(PayFrequency::Weekly->periods())->format(),
            'keep_percent' => $comparison['keep_percent'],
            'rows' => [
                $row('Gross salary', $current, $new),
                $row('Income tax', $taxBefore, $taxAfter),
                $row($pensionLabel, $pensionBefore, $pensionAfter),
                $row($insuranceLabel, $insuranceBefore, $insuranceAfter),
                $row('Net annual pay', $baseline->metrics['net_annual'], $modified->metrics['net_annual']),
                $row('Net monthly pay', $baseline->metrics['net_annual']->divideBy(12), $modified->metrics['net_annual']->divideBy(12)),
                $row('Net biweekly pay', $baseline->metrics['net_annual']->divideBy(26), $modified->metrics['net_annual']->divideBy(26)),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.raise-calculator', [
            'provinces' => Province::all(),
        ]);
    }
}
