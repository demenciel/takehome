<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Family\ParentalLeaveProjectionService;
use App\Support\FamilyFunnel;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class ParentalLeaveCalculator extends Component
{
    use FamilyFunnel;

    public string $province = 'ON';

    public string $salary = '80000';

    public string $frequency = 'biweekly';

    public string $who = 'birth_parent';

    public string $leaveType = 'maternity_standard';

    public string $plannedWeeks = '50';

    public bool $topUpEnabled = false;

    public string $topUpPercent = '80';

    public string $topUpWeeks = '17';

    public string $topUpMode = 'to_percent';

    public string $partnerIncome = '';

    public string $partnerWeeks = '';

    public string $monthlyExpenses = '';

    public string $monthlySavings = '';

    public bool $showHousehold = false;

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
            'tool_key' => $this->toolKey(),
            'province' => $this->province,
            'path' => request()->path(),
        ]);
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('parental_leave_calculator_started', [
            'tool_key' => $this->toolKey(),
            'path' => request()->path(),
        ]);

        $this->validate([
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'salary' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'frequency' => ['required', Rule::in(array_map(fn (PayFrequency $frequency) => $frequency->value, PayFrequency::supported()))],
            'who' => ['required', Rule::in(['birth_parent', 'other_parent', 'both'])],
            'leaveType' => ['required', Rule::in(['maternity_standard', 'maternity_extended', 'standard', 'extended'])],
            'plannedWeeks' => ['required', 'integer', 'min:1', 'max:80'],
            'topUpPercent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'topUpWeeks' => ['nullable', 'integer', 'min:0', 'max:80'],
            'topUpMode' => ['required', Rule::in(['to_percent', 'add_percent'])],
            'partnerIncome' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'partnerWeeks' => ['nullable', 'integer', 'min:0', 'max:80'],
            'monthlyExpenses' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'monthlySavings' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
        ]);

        try {
            $result = app(ParentalLeaveProjectionService::class)->project([
                'province' => $this->province,
                'salary' => $this->salary,
                'frequency' => $this->frequency,
                'who' => $this->who,
                'leave_type' => $this->leaveType,
                'planned_weeks' => (int) $this->plannedWeeks,
                'top_up_enabled' => $this->topUpEnabled,
                'top_up_percent' => $this->topUpPercent,
                'top_up_weeks' => (int) $this->topUpWeeks,
                'top_up_mode' => $this->topUpMode,
                'partner_income' => $this->partnerIncome,
                'partner_weeks' => $this->partnerWeeks,
                'monthly_expenses' => $this->monthlyExpenses,
                'monthly_savings' => $this->monthlySavings,
            ]);

            $this->resultPayload = $this->serialize($result);

            app(Analytics::class)->record('parental_leave_calculation_completed', [
                'tool_key' => $this->toolKey(),
                'province' => $this->province,
                'frequency' => $this->resultPayload['program_label'] ?? null,
                'annual_salary_cents' => Money::fromDollars($this->salary)->cents,
                'path' => request()->path(),
            ]);

            if (($this->resultPayload['supported'] ?? false) && filled(config('external-links.etsy.parental_leave_planner'))) {
                $this->trackFunnel('parental_planner_cta_view', 'after_results');
            }
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    public function continueToBabyBudget(): mixed
    {
        $this->trackFunnel('parental_to_baby_click', 'after_results');

        if ($this->resultPayload && ($this->resultPayload['supported'] ?? false)) {
            session([
                'family.monthly_leave_reduction' => preg_replace('/[^0-9.]/', '', $this->resultPayload['reduction_monthly'] ?? ''),
            ]);
        }

        return $this->redirect(route('tools.baby'), navigate: false);
    }

    public function markPlannerClick(): void
    {
        $this->trackFunnel('parental_planner_cta_click', 'after_results');
    }

    public function markEiClick(): void
    {
        $this->trackFunnel('parental_to_ei_click', 'after_results');
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function serialize(array $result): array
    {
        if (! ($result['supported'] ?? false)) {
            return [
                'supported' => false,
                'quebec' => true,
                'qpip_url' => $result['qpip_url'],
                'warnings' => $result['warnings'],
                'employment' => $this->serializeEmployment($result['employment']),
                'year' => $result['year'],
                'reviewed' => $result['reviewed'],
            ];
        }

        $money = fn (?Money $value) => $value?->format() ?? '$0.00';

        return [
            'supported' => true,
            'quebec' => false,
            'program_label' => $result['program_label'],
            'leave_weeks' => $result['leave_weeks'],
            'maternity_weeks' => $result['split']['maternity_weeks'],
            'parental_weeks' => $result['split']['parental_weeks'],
            'employment' => $this->serializeEmployment($result['employment']),
            'weekly_ei' => $money($result['weekly_ei']),
            'monthly_ei' => $money($result['monthly_ei']),
            'ei_total' => $money($result['ei_total']),
            'parental_rate' => $result['parental']['rate_percent'].'%',
            'parental_max' => $money($result['parental']['max_weekly']),
            'capped' => $result['parental']['capped'] || ($result['maternity']['capped'] ?? false),
            'top_up_enabled' => $result['top_up']['enabled'],
            'top_up_weekly' => $money($result['top_up']['weekly']),
            'top_up_weeks' => $result['top_up']['weeks'],
            'top_up_total' => $money($result['top_up']['total']),
            'leave_weekly' => $money($result['leave_weekly']),
            'leave_monthly' => $money($result['leave_monthly']),
            'leave_total' => $money($result['leave_total']),
            'working_monthly' => $money($result['employment']['net_monthly']),
            'reduction_monthly' => $money($result['reduction_monthly']),
            'reduction_percent' => $result['reduction_percent'],
            'income_difference' => $money($result['income_difference']),
            'standard_weekly' => $money($result['comparison']['standard']['weekly']),
            'standard_monthly' => $money($result['comparison']['standard']['monthly']),
            'standard_weeks' => $result['comparison']['standard']['weeks'],
            'standard_total' => $money($result['comparison']['standard']['total']),
            'extended_weekly' => $money($result['comparison']['extended']['weekly']),
            'extended_monthly' => $money($result['comparison']['extended']['monthly']),
            'extended_weeks' => $result['comparison']['extended']['weeks'],
            'extended_total' => $money($result['comparison']['extended']['total']),
            'household' => $result['household']['included'] ? [
                'before' => $money($result['household']['before']),
                'during' => $money($result['household']['during']),
                'expenses' => $money($result['household']['expenses']),
                'monthly_shortfall' => $money($result['household']['monthly_shortfall']),
                'total_shortfall' => $money($result['household']['total_shortfall']),
                'buffer' => $money($result['household']['buffer']),
            ] : null,
            'warnings' => $result['warnings'],
            'year' => $result['year'],
            'reviewed' => $result['reviewed'],
            'planner_url' => config('external-links.etsy.parental_leave_planner'),
        ];
    }

    /**
     * @param  array<string, mixed>  $employment
     * @return array<string, string>
     */
    private function serializeEmployment(array $employment): array
    {
        return [
            'gross_weekly' => $employment['gross_weekly']->format(),
            'gross_biweekly' => $employment['gross_biweekly']->format(),
            'gross_monthly' => $employment['gross_monthly']->format(),
            'net_monthly' => $employment['net_monthly']->format(),
            'net_period' => $employment['net_period']->format(),
            'frequency' => $employment['frequency']->label(),
        ];
    }

    protected function toolKey(): string
    {
        return 'parental';
    }

    protected function funnelContext(): ?string
    {
        return str_contains($this->leaveType, 'extended') ? 'extended' : 'standard';
    }

    public function render()
    {
        return view('livewire.parental-leave-calculator', [
            'provinces' => Province::all(),
            'frequencies' => PayFrequency::supported(),
        ]);
    }
}
