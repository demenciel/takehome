<?php

namespace App\Livewire;

use App\Services\Analytics\Analytics;
use App\Services\Family\ParentalBenefitsService;
use App\Services\Family\ParentalLeaveProjectionService;
use App\Support\FamilyFunnel;
use App\Support\Money;
use App\Support\Province;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class EiMaternityParentalCalculator extends Component
{
    use FamilyFunnel;

    public string $province = 'ON';

    public string $inputMode = 'annual';

    public string $amount = '80000';

    public string $program = 'maternity';

    public string $weeks = '15';

    public bool $sharing = false;

    public string $parentA = '20';

    public string $parentB = '15';

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

    public function updatedProgram(): void
    {
        $this->weeks = match ($this->program) {
            'maternity' => '15',
            'standard_parental' => '35',
            default => '61',
        };
    }

    public function calculate(): void
    {
        $this->errorMessage = null;
        $this->hasCalculated = true;

        app(Analytics::class)->record('ei_benefit_calculator_started', [
            'tool_key' => $this->toolKey(),
            'path' => request()->path(),
        ]);

        $this->validate([
            'province' => ['required', Rule::in(array_map(fn (Province $province) => $province->value, Province::all()))],
            'inputMode' => ['required', Rule::in(['annual', 'weekly'])],
            'amount' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'program' => ['required', Rule::in(['maternity', 'standard_parental', 'extended_parental'])],
            'weeks' => ['required', 'integer', 'min:1', 'max:80'],
            'parentA' => ['nullable', 'integer', 'min:0', 'max:80'],
            'parentB' => ['nullable', 'integer', 'min:0', 'max:80'],
        ]);

        $province = Province::fromCode($this->province);

        if ($province?->usesQpp()) {
            $this->resultPayload = [
                'quebec' => true,
                'qpip_url' => config('external-links.government.qpip'),
            ];

            app(Analytics::class)->record('ei_benefit_calculation_completed', [
                'tool_key' => $this->toolKey(),
                'province' => $this->province,
                'frequency' => 'qpip',
                'path' => request()->path(),
            ]);

            return;
        }

        try {
            $amount = Money::fromDollars($this->amount);
            $estimate = app(ParentalLeaveProjectionService::class)->eiOnly(
                $amount,
                $this->program,
                (int) $this->weeks,
                $this->inputMode === 'weekly',
                $this->sharing,
                (int) $this->parentA,
                (int) $this->parentB,
            );

            $this->resultPayload = [
                'quebec' => false,
                'weekly_benefit' => $estimate['weekly_benefit']->format(),
                'rate_percent' => $estimate['rate_percent'].'%',
                'max_weekly' => $estimate['max_weekly']->format(),
                'capped' => $estimate['capped'],
                'monthly_equivalent' => $estimate['monthly_equivalent']->format(),
                'weeks' => $estimate['weeks'],
                'max_weeks' => $estimate['max_weeks'],
                'shared_max_weeks' => $estimate['shared_max_weeks'],
                'total' => $estimate['total']->format(),
                'warnings' => $estimate['warnings'],
                'program_label' => match ($this->program) {
                    'maternity' => 'maternity',
                    'extended_parental' => 'extended',
                    default => 'standard',
                },
                'planner_url' => config('external-links.etsy.parental_leave_planner'),
            ];

            app(Analytics::class)->record('ei_benefit_calculation_completed', [
                'tool_key' => $this->toolKey(),
                'province' => $this->province,
                'frequency' => $this->resultPayload['program_label'],
                'annual_salary_cents' => $this->inputMode === 'weekly' ? $amount->multiply('52')->cents : $amount->cents,
                'path' => request()->path(),
            ]);

            if (filled(config('external-links.etsy.parental_leave_planner'))) {
                $this->trackFunnel('ei_parental_planner_cta_view', 'after_results');
            }
        } catch (Throwable) {
            $this->resultPayload = null;
            $this->errorMessage = __('calculator.error_generic');
        }
    }

    public function markParentalClick(): void
    {
        $this->trackFunnel('ei_to_parental_calculator_click', 'after_results');
    }

    public function markPlannerClick(): void
    {
        $this->trackFunnel('ei_parental_planner_cta_click', 'after_results');
    }

    public function markBabyClick(): void
    {
        $this->trackFunnel('ei_to_baby_click', 'after_results');
    }

    protected function toolKey(): string
    {
        return 'ei_benefits';
    }

    protected function funnelContext(): ?string
    {
        return match ($this->program) {
            'extended_parental' => 'extended',
            'maternity' => 'maternity',
            default => 'standard',
        };
    }

    public function render()
    {
        return view('livewire.ei-maternity-parental-calculator', [
            'provinces' => Province::all(),
            'year' => app(ParentalBenefitsService::class)->year(),
        ]);
    }
}
