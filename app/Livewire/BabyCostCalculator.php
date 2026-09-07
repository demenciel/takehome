<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Family\BabyBudgetService;
use App\Support\FamilyFunnel;
use Livewire\Component;
use Throwable;

class BabyCostCalculator extends Component
{
    use FamilyFunnel;

    public ?string $province = null;

    /** @var list<array<string, mixed>> */
    public array $startup = [];

    /** @var list<array<string, mixed>> */
    public array $recurring = [];

    public bool $childcareNeeded = false;

    public string $childcareStartMonth = '7';

    public string $childcareMonthly = '';

    public string $childcareSubsidy = '';

    public string $ccbMonthly = '';

    public string $currentSavings = '';

    public string $monthlySaved = '';

    public string $monthsUntil = '6';

    public string $leaveReduction = '';

    public string $leaveMonths = '12';

    public ?array $resultPayload = null;

    public ?string $errorMessage = null;

    public bool $hasCalculated = false;

    public function mount(bool $autoCalculate = false): void
    {
        $service = app(BabyBudgetService::class);
        $this->startup = $service->defaultStartupState();
        $this->recurring = $service->defaultRecurringState();

        $fromLeave = session('family.monthly_leave_reduction');

        if (is_numeric($fromLeave)) {
            $this->leaveReduction = (string) $fromLeave;
        }

        if ($autoCalculate) {
            $this->calculate();
        }
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('baby_cost_calculator_started', [
            'tool_key' => $this->toolKey(),
            'path' => request()->path(),
        ]);

        $this->validate([
            'startup' => ['required', 'array'],
            'startup.*.planned' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'startup.*.actual' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'startup.*.used_cost' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'recurring.*.monthly' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'childcareStartMonth' => ['nullable', 'integer', 'min:1', 'max:12'],
            'childcareMonthly' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'childcareSubsidy' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'ccbMonthly' => ['nullable', 'numeric', 'min:0', 'max:20000'],
            'currentSavings' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'monthlySaved' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'monthsUntil' => ['nullable', 'integer', 'min:0', 'max:36'],
            'leaveReduction' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'leaveMonths' => ['nullable', 'integer', 'min:0', 'max:24'],
        ]);

        try {
            $summary = app(BabyBudgetService::class)->summarize(
                $this->startup,
                $this->recurring,
                [
                    'needed' => $this->childcareNeeded,
                    'start_month' => $this->childcareStartMonth,
                    'monthly' => $this->childcareMonthly,
                    'subsidy' => $this->childcareSubsidy,
                ],
                [
                    'ccb_monthly' => $this->ccbMonthly,
                    'current_savings' => $this->currentSavings,
                    'monthly_saved' => $this->monthlySaved,
                    'months_until' => $this->monthsUntil,
                    'leave_reduction' => $this->leaveReduction,
                    'leave_months' => $this->leaveMonths,
                ],
            );

            $money = fn ($value) => $value->format();

            $this->resultPayload = [
                'planned_startup' => $money($summary['planned_startup']),
                'already_spent' => $money($summary['already_spent']),
                'gift_savings' => $money($summary['gift_savings']),
                'used_savings' => $money($summary['used_savings']),
                'remaining_purchases' => $money($summary['remaining_purchases']),
                'monthly_recurring' => $money($summary['monthly_recurring']),
                'recurring_first_year' => $money($summary['recurring_first_year']),
                'childcare_first_year' => $money($summary['childcare_first_year']),
                'childcare_monthly' => $money($summary['childcare_monthly']),
                'current_savings' => $money($summary['current_savings']),
                'projected_savings' => $money($summary['projected_savings']),
                'first_year_cost' => $money($summary['first_year_cost']),
                'amount_left' => $money($summary['amount_left_to_prepare']),
                'surplus' => $money($summary['surplus']),
                'planner_url' => config('external-links.etsy.new_baby_planner'),
            ];

            app(Analytics::class)->record('baby_cost_calculation_completed', [
                'tool_key' => $this->toolKey(),
                'path' => request()->path(),
            ]);

            if (filled(config('external-links.etsy.new_baby_planner'))) {
                $this->trackFunnel('baby_planner_cta_view', 'after_results');
            }
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    public function markPlannerClick(): void
    {
        $this->trackFunnel('baby_planner_cta_click', 'after_results');
    }

    public function markParentalClick(): void
    {
        $this->trackFunnel('baby_to_parental_click', 'after_results');
    }

    public function markEiClick(): void
    {
        $this->trackFunnel('baby_to_ei_click', 'after_results');
    }

    protected function toolKey(): string
    {
        return 'baby';
    }

    public function render()
    {
        $service = app(BabyBudgetService::class);

        return view('livewire.baby-cost-calculator', [
            'ccb' => $service->ccbReference(),
            'disclaimer' => $service->disclaimer(),
        ]);
    }
}
