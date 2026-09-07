<?php

namespace App\Services\Family;

use App\Support\Money;
use Carbon\CarbonImmutable;

class BabyBudgetService
{
    /**
     * @return list<array{id: string, category: string, label: string, planned: string}>
     */
    public function startupDefaults(): array
    {
        return config('baby-budget.startup', []);
    }

    /**
     * @return list<array{id: string, label: string, monthly: string}>
     */
    public function recurringDefaults(): array
    {
        return config('baby-budget.recurring', []);
    }

    /**
     * @return array{period_label: string, under_6_annual_maximum: string, under_6_monthly_maximum: string, note: string}
     */
    public function ccbReference(): array
    {
        return config('baby-budget.ccb');
    }

    public function lastReviewedLabel(): string
    {
        $date = config('baby-budget.last_reviewed');

        return $date ? CarbonImmutable::parse((string) $date)->format('F j, Y') : '';
    }

    public function disclaimer(): string
    {
        return (string) config('baby-budget.disclaimer');
    }

    /**
     * @param  list<array<string, mixed>>  $startup
     * @param  list<array<string, mixed>>  $recurring
     * @param  array<string, mixed>  $childcare
     * @param  array<string, mixed>  $readiness
     * @return array<string, mixed>
     */
    public function summarize(array $startup, array $recurring, array $childcare = [], array $readiness = []): array
    {
        $planned = Money::zero();
        $spent = Money::zero();
        $giftSavings = Money::zero();
        $usedSavings = Money::zero();
        $remaining = Money::zero();

        foreach ($startup as $item) {
            if ($this->flag($item['skip'] ?? false)) {
                continue;
            }

            $linePlanned = $this->money($item['planned'] ?? 0);
            $actual = $this->money($item['actual'] ?? 0);
            $usedCost = $this->money($item['used_cost'] ?? 0);
            $purchased = $this->flag($item['purchased'] ?? false);
            $gift = $this->flag($item['gift'] ?? false);
            $used = $this->flag($item['used'] ?? false);

            $planned = $planned->add($linePlanned);

            if ($gift) {
                $giftSavings = $giftSavings->add($linePlanned);

                continue;
            }

            if ($used && $usedCost->isPositive() && $linePlanned->greaterThan($usedCost)) {
                $usedSavings = $usedSavings->add($linePlanned->subtract($usedCost));
            }

            if ($purchased) {
                $spent = $spent->add($actual->isPositive() ? $actual : ($used && $usedCost->isPositive() ? $usedCost : $linePlanned));

                continue;
            }

            $remaining = $remaining->add($used && $usedCost->isPositive() ? $usedCost : $linePlanned);
        }

        $monthlyRecurring = Money::zero();

        foreach ($recurring as $item) {
            $monthlyRecurring = $monthlyRecurring->add($this->money($item['monthly'] ?? 0));
        }

        $recurringFirstYear = $monthlyRecurring->multiply('12');

        $childcareNeeded = $this->flag($childcare['needed'] ?? false);
        $startMonth = max(1, min(12, (int) ($childcare['start_month'] ?? 1)));
        $childcareMonths = $childcareNeeded ? (13 - $startMonth) : 0;
        $childcareGrossMonthly = $this->money($childcare['monthly'] ?? 0);
        $subsidy = $this->money($childcare['subsidy'] ?? 0);
        $childcareNetMonthly = $childcareGrossMonthly->subtract($subsidy);

        if ($childcareNetMonthly->isNegative()) {
            $childcareNetMonthly = Money::zero();
        }

        $childcareFirstYear = $childcareNeeded
            ? $childcareNetMonthly->multiply((string) $childcareMonths)
            : Money::zero();

        $ccbMonthly = $this->money($readiness['ccb_monthly'] ?? 0);
        $currentSavings = $this->money($readiness['current_savings'] ?? 0);
        $monthlySaved = $this->money($readiness['monthly_saved'] ?? 0);
        $monthsUntil = max(0, (int) ($readiness['months_until'] ?? 0));
        $leaveReduction = $this->money($readiness['leave_reduction'] ?? 0);
        $projectedSavings = $currentSavings->add($monthlySaved->multiply((string) $monthsUntil));
        $firstYearCost = $remaining->add($recurringFirstYear)->add($childcareFirstYear);
        $available = $projectedSavings->add($ccbMonthly->multiply('12'));
        $gap = $firstYearCost->add($leaveReduction->multiply((string) max(0, (int) ($readiness['leave_months'] ?? 0))))->subtract($available);

        return [
            'planned_startup' => $planned,
            'already_spent' => $spent,
            'gift_savings' => $giftSavings,
            'used_savings' => $usedSavings,
            'remaining_purchases' => $remaining,
            'monthly_recurring' => $monthlyRecurring,
            'recurring_first_year' => $recurringFirstYear,
            'childcare_needed' => $childcareNeeded,
            'childcare_months' => $childcareMonths,
            'childcare_monthly' => $childcareNetMonthly,
            'childcare_first_year' => $childcareFirstYear,
            'ccb_monthly' => $ccbMonthly,
            'current_savings' => $currentSavings,
            'projected_savings' => $projectedSavings,
            'first_year_cost' => $firstYearCost,
            'funding_gap' => $gap,
            'amount_left_to_prepare' => $gap->isPositive() ? $gap : Money::zero(),
            'surplus' => $gap->isNegative() ? $gap->abs() : Money::zero(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultStartupState(): array
    {
        return array_map(fn (array $item) => [
            ...$item,
            'actual' => '',
            'purchased' => false,
            'gift' => false,
            'used' => false,
            'used_cost' => '',
            'skip' => false,
        ], $this->startupDefaults());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultRecurringState(): array
    {
        return $this->recurringDefaults();
    }

    private function money(mixed $value): Money
    {
        if ($value instanceof Money) {
            return $value;
        }

        if ($value === null || $value === '' || ! is_numeric($value)) {
            return Money::zero();
        }

        return Money::fromDollars($value);
    }

    private function flag(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true';
    }
}
