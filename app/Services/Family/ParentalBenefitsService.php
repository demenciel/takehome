<?php

namespace App\Services\Family;

use App\Support\Money;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class ParentalBenefitsService
{
    public function year(): int
    {
        return (int) config('benefits.year', config('tax.current_year'));
    }

    public function lastReviewedLabel(): string
    {
        $date = config('benefits.last_reviewed');

        return $date ? CarbonImmutable::parse((string) $date)->format('F j, Y') : (string) $this->year();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $year = $this->year();
        $rules = config('benefits.years.'.$year.'.ei');

        if (! is_array($rules)) {
            throw new InvalidArgumentException("No EI parental-benefit rules for {$year}.");
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function program(string $key): array
    {
        $rules = $this->rules();

        if (! isset($rules[$key]) || ! is_array($rules[$key])) {
            throw new InvalidArgumentException("Unknown EI parental program [{$key}].");
        }

        return $rules[$key];
    }

    /**
     * @return list<array{title: string, url: string, publisher?: string}>
     */
    public function sources(): array
    {
        return config('benefits.sources', []);
    }

    public function disclaimer(): string
    {
        return (string) config('benefits.disclaimer');
    }

    public function maximumInsurableEarnings(): Money
    {
        return Money::fromDollars($this->rules()['maximum_insurable_earnings']);
    }

    public function weeklyInsurableEarnings(Money $annualSalary): Money
    {
        $fromSalary = $annualSalary->divideBy(52);
        $fromMaximum = $this->maximumInsurableEarnings()->divideBy(52);

        return $fromSalary->min($fromMaximum);
    }

    public function weeklyBenefit(Money $annualSalary, string $program): Money
    {
        $rules = $this->program($program);
        $insurable = $this->weeklyInsurableEarnings($annualSalary);
        $maximum = Money::fromDollars($rules['max_weekly']);

        if ($insurable->equals($this->maximumInsurableEarnings()->divideBy(52))) {
            return $maximum;
        }

        return $insurable->multiply((string) $rules['rate'])->min($maximum);
    }

    /**
     * @return array{
     *     program: string,
     *     rate: string,
     *     rate_percent: string,
     *     weekly_insurable: Money,
     *     weekly_benefit: Money,
     *     max_weekly: Money,
     *     capped: bool,
     *     monthly_equivalent: Money,
     *     weeks: int,
     *     max_weeks: int,
     *     total: Money,
     *     warnings: list<string>
     * }
     */
    public function estimate(Money $annualSalary, string $program, int $weeks, ?int $sharedWeeks = null, ?int $otherParentWeeks = null): array
    {
        if ($weeks < 0) {
            throw new InvalidArgumentException('Weeks cannot be negative.');
        }

        $rules = $this->program($program);
        $weekly = $this->weeklyBenefit($annualSalary, $program);
        $maxWeekly = Money::fromDollars($rules['max_weekly']);
        $atInsurableMax = $this->weeklyInsurableEarnings($annualSalary)
            ->equals($this->maximumInsurableEarnings()->divideBy(52));
        $individualMax = (int) ($rules['individual_max_weeks'] ?? $rules['max_weeks']);
        $sharedMax = (int) ($rules['shared_max_weeks'] ?? $individualMax);
        $warnings = [];

        $used = $weeks;

        if ($used > $individualMax) {
            $warnings[] = "One parent can usually receive at most {$individualMax} weeks of this benefit. The estimate uses {$individualMax} weeks.";
            $used = $individualMax;
        }

        if ($otherParentWeeks !== null) {
            $combined = $used + max(0, $otherParentWeeks);

            if ($combined > $sharedMax) {
                $warnings[] = "Parents can usually share at most {$sharedMax} weeks. Combined weeks were reduced for this estimate.";
                $used = max(0, $sharedMax - max(0, $otherParentWeeks));
            }
        } elseif ($sharedWeeks !== null && $sharedWeeks > $sharedMax) {
            $warnings[] = "Parents can usually share at most {$sharedMax} weeks.";
        }

        return [
            'program' => $program,
            'rate' => (string) $rules['rate'],
            'rate_percent' => number_format(((float) $rules['rate']) * 100, 0),
            'weekly_insurable' => $this->weeklyInsurableEarnings($annualSalary),
            'weekly_benefit' => $weekly,
            'max_weekly' => $maxWeekly,
            'capped' => $atInsurableMax || $weekly->equals($maxWeekly),
            'monthly_equivalent' => $weekly->multiply('52')->divideBy(12),
            'weeks' => $used,
            'requested_weeks' => $weeks,
            'max_weeks' => $individualMax,
            'shared_max_weeks' => $sharedMax,
            'total' => $weekly->multiply((string) $used),
            'warnings' => $warnings,
        ];
    }

    /**
     * Split a combined maternity + parental leave into weeks at each rate.
     *
     * @return array{maternity_weeks: int, parental_weeks: int, warnings: list<string>}
     */
    public function splitLeave(string $leaveType, int $plannedWeeks, int $partnerWeeks = 0): array
    {
        $includesMaternity = in_array($leaveType, ['maternity_standard', 'maternity_extended'], true);
        $parentalProgram = str_contains($leaveType, 'extended') ? 'extended_parental' : 'standard_parental';
        $maternityMax = (int) $this->program('maternity')['max_weeks'];
        $parental = $this->program($parentalProgram);
        $individualMax = (int) $parental['individual_max_weeks'];
        $sharedMax = (int) $parental['shared_max_weeks'];
        $warnings = [];

        $remaining = max(0, $plannedWeeks);
        $maternityWeeks = 0;

        if ($includesMaternity) {
            $maternityWeeks = min($maternityMax, $remaining);
            $remaining -= $maternityWeeks;
        }

        $parentalWeeks = $remaining;

        if ($parentalWeeks > $individualMax) {
            $warnings[] = "One parent can usually receive at most {$individualMax} parental weeks. Extra weeks are not counted in the EI total.";
            $parentalWeeks = $individualMax;
        }

        if ($partnerWeeks > 0 && ($parentalWeeks + $partnerWeeks) > $sharedMax) {
            $warnings[] = "Parents can usually share at most {$sharedMax} parental weeks. Combined parental weeks were reduced for this estimate.";
            $parentalWeeks = max(0, $sharedMax - $partnerWeeks);
        }

        if ($plannedWeeks < 1) {
            $warnings[] = 'Enter at least one planned leave week to estimate benefits.';
        }

        return [
            'maternity_weeks' => $maternityWeeks,
            'parental_weeks' => $parentalWeeks,
            'parental_program' => $parentalProgram,
            'leave_type' => $leaveType,
            'warnings' => $warnings,
        ];
    }
}
