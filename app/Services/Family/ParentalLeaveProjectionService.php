<?php

namespace App\Services\Family;

use App\Calculators\Payroll\PayrollCalculator;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use InvalidArgumentException;

class ParentalLeaveProjectionService
{
    public function __construct(
        private ParentalBenefitsService $benefits,
        private PayrollCalculator $payroll,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function project(array $input): array
    {
        $province = $input['province'] ?? null;

        if (is_string($province)) {
            $province = Province::fromCode($province) ?? Province::fromSlug($province);
        }

        if (! $province instanceof Province) {
            throw new InvalidArgumentException('A province or territory is required.');
        }

        $salary = $input['salary'] instanceof Money ? $input['salary'] : Money::fromDollars($input['salary'] ?? 0);
        $frequencyInput = $input['frequency'] ?? 'biweekly';
        $frequency = $frequencyInput instanceof PayFrequency
            ? $frequencyInput
            : PayFrequency::tryFrom((string) $frequencyInput) ?? PayFrequency::Biweekly;

        if ($province->usesQpp()) {
            return $this->quebecResponse($province, $salary, $frequency);
        }

        $leaveType = (string) ($input['leave_type'] ?? 'maternity_standard');
        $plannedWeeks = (int) ($input['planned_weeks'] ?? 0);
        $partnerWeeks = (int) ($input['partner_weeks'] ?? 0);
        $split = $this->benefits->splitLeave($leaveType, $plannedWeeks, $partnerWeeks);

        $employment = $this->employmentIncome($province, $salary, $frequency);
        $maternity = $split['maternity_weeks'] > 0
            ? $this->benefits->estimate($salary, 'maternity', $split['maternity_weeks'])
            : null;
        $parental = $this->benefits->estimate($salary, $split['parental_program'], $split['parental_weeks']);

        $eiTotal = ($maternity['total'] ?? Money::zero())->add($parental['total']);
        $leaveWeeks = $split['maternity_weeks'] + $split['parental_weeks'];
        $averageWeeklyEi = $leaveWeeks > 0 ? $eiTotal->divideBy($leaveWeeks) : Money::zero();

        $topUp = $this->topUp(
            $employment['gross_weekly'],
            $averageWeeklyEi,
            (bool) ($input['top_up_enabled'] ?? false),
            (string) ($input['top_up_percent'] ?? '0'),
            (int) ($input['top_up_weeks'] ?? 0),
            (string) ($input['top_up_mode'] ?? 'to_percent'),
        );

        $leaveWeekly = $averageWeeklyEi->add($leaveWeeks > 0 ? $topUp['total']->divideBy($leaveWeeks) : Money::zero());
        $leaveMonthly = $leaveWeekly->multiply('52')->divideBy(12);
        $reductionMonthly = $employment['net_monthly']->subtract($leaveMonthly);
        $reductionPercent = $employment['net_monthly']->isZero()
            ? '0.0'
            : number_format(($reductionMonthly->cents / $employment['net_monthly']->cents) * 100, 1);

        $comparison = $this->standardVersusExtended($salary, $leaveType, $plannedWeeks, $partnerWeeks);
        $household = $this->household(
            $province,
            $employment,
            $leaveMonthly,
            $leaveWeeks,
            $input,
        );

        $warnings = array_values(array_unique([
            ...$split['warnings'],
            ...($maternity['warnings'] ?? []),
            ...$parental['warnings'],
            $this->benefits->disclaimer(),
        ]));

        return [
            'supported' => true,
            'quebec' => false,
            'province' => $province,
            'leave_type' => $leaveType,
            'program_label' => str_contains($leaveType, 'extended') ? 'extended' : 'standard',
            'who' => (string) ($input['who'] ?? 'birth_parent'),
            'employment' => $employment,
            'split' => $split,
            'maternity' => $maternity,
            'parental' => $parental,
            'weekly_ei' => $averageWeeklyEi,
            'monthly_ei' => $averageWeeklyEi->multiply('52')->divideBy(12),
            'ei_total' => $eiTotal,
            'leave_weeks' => $leaveWeeks,
            'top_up' => $topUp,
            'leave_weekly' => $leaveWeekly,
            'leave_monthly' => $leaveMonthly,
            'leave_total' => $leaveWeekly->multiply((string) $leaveWeeks),
            'reduction_monthly' => $reductionMonthly,
            'reduction_percent' => $reductionPercent,
            'income_difference' => $employment['net_weekly']->multiply((string) $leaveWeeks)->subtract($leaveWeekly->multiply((string) $leaveWeeks)),
            'comparison' => $comparison,
            'household' => $household,
            'warnings' => $warnings,
            'year' => $this->benefits->year(),
            'reviewed' => $this->benefits->lastReviewedLabel(),
            'sources' => $this->benefits->sources(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function eiOnly(Money $annualOrWeekly, string $program, int $weeks, bool $fromWeekly, bool $sharing = false, int $parentA = 0, int $parentB = 0): array
    {
        $annual = $fromWeekly ? $annualOrWeekly->multiply('52') : $annualOrWeekly;
        $other = $sharing ? ($program === 'maternity' ? 0 : $parentB) : null;
        $weeks = $sharing && $program !== 'maternity' ? $parentA : $weeks;

        return $this->benefits->estimate($annual, $program, $weeks, otherParentWeeks: $other);
    }

    /**
     * @return array<string, mixed>
     */
    private function employmentIncome(Province $province, Money $salary, PayFrequency $frequency): array
    {
        $payroll = $this->payroll->calculate([
            'annual_salary' => $salary->dollars(),
            'province' => $province->value,
            'frequency' => $frequency->value,
        ]);

        return [
            'gross_annual' => $salary,
            'gross_weekly' => $salary->divideBy(52),
            'gross_biweekly' => $salary->divideBy(26),
            'gross_monthly' => $salary->divideBy(12),
            'net_annual' => $payroll->metrics['net_annual'],
            'net_weekly' => $payroll->metrics['net_annual']->divideBy(52),
            'net_monthly' => $payroll->metrics['net_annual']->divideBy(12),
            'net_period' => $payroll->metrics['net_period'],
            'frequency' => $frequency,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topUp(Money $grossWeekly, Money $eiWeekly, bool $enabled, string $percent, int $weeks, string $mode): array
    {
        if (! $enabled || $weeks < 1 || ! is_numeric($percent) || (float) $percent <= 0) {
            return [
                'enabled' => false,
                'mode' => $mode,
                'percent' => $percent,
                'weeks' => 0,
                'weekly' => Money::zero(),
                'total' => Money::zero(),
                'combined_weekly' => $eiWeekly,
            ];
        }

        $rate = bcdiv((string) $percent, '100', 8);
        $weekly = $mode === 'add_percent'
            ? $grossWeekly->multiply($rate)
            : $grossWeekly->multiply($rate)->subtract($eiWeekly);

        if ($weekly->isNegative()) {
            $weekly = Money::zero();
        }

        return [
            'enabled' => true,
            'mode' => $mode,
            'percent' => $percent,
            'weeks' => $weeks,
            'weekly' => $weekly,
            'total' => $weekly->multiply((string) $weeks),
            'combined_weekly' => $eiWeekly->add($weekly),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function standardVersusExtended(Money $salary, string $leaveType, int $plannedWeeks, int $partnerWeeks): array
    {
        $includesMaternity = in_array($leaveType, ['maternity_standard', 'maternity_extended'], true);
        $standard = $this->pathTotals($salary, 'maternity_standard', $includesMaternity ? max($plannedWeeks, 50) : max($plannedWeeks, 35), $partnerWeeks);
        $extended = $this->pathTotals($salary, 'maternity_extended', $includesMaternity ? max($plannedWeeks, 76) : max($plannedWeeks, 61), $partnerWeeks);

        if (! $includesMaternity && in_array($leaveType, ['standard', 'extended'], true)) {
            $standard = $this->pathTotals($salary, 'standard', max($plannedWeeks, 35), $partnerWeeks);
            $extended = $this->pathTotals($salary, 'extended', max($plannedWeeks, 61), $partnerWeeks);
        }

        return [
            'standard' => $standard,
            'extended' => $extended,
        ];
    }

    /**
     * @return array{weekly: Money, monthly: Money, weeks: int, total: Money, label: string}
     */
    private function pathTotals(Money $salary, string $leaveType, int $weeks, int $partnerWeeks): array
    {
        $split = $this->benefits->splitLeave($leaveType, $weeks, $partnerWeeks);
        $maternity = $split['maternity_weeks'] > 0
            ? $this->benefits->estimate($salary, 'maternity', $split['maternity_weeks'])['total']
            : Money::zero();
        $parental = $this->benefits->estimate($salary, $split['parental_program'], $split['parental_weeks'])['total'];
        $total = $maternity->add($parental);
        $used = $split['maternity_weeks'] + $split['parental_weeks'];
        $weekly = $used > 0 ? $total->divideBy($used) : Money::zero();

        return [
            'weekly' => $weekly,
            'monthly' => $weekly->multiply('52')->divideBy(12),
            'weeks' => $used,
            'total' => $total,
            'label' => str_contains($leaveType, 'extended') ? 'Extended' : 'Standard',
        ];
    }

    /**
     * @param  array<string, mixed>  $employment
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function household(Province $province, array $employment, Money $leaveMonthly, int $leaveWeeks, array $input): array
    {
        $partnerSalary = $this->optionalMoney($input['partner_income'] ?? null);
        $expenses = $this->optionalMoney($input['monthly_expenses'] ?? null);
        $savingsTarget = $this->optionalMoney($input['monthly_savings'] ?? null);
        $partnerWeeks = (int) ($input['partner_weeks'] ?? 0);

        if ($partnerSalary === null && $expenses === null) {
            return ['included' => false];
        }

        $partnerWorking = $this->partnerWorkingMonthly($province, $partnerSalary);
        $partnerDuring = $partnerWorking;

        if ($partnerSalary !== null && $partnerSalary->isPositive() && $partnerWeeks > 0) {
            $partnerDuring = $this->benefits
                ->weeklyBenefit($partnerSalary, str_contains((string) ($input['leave_type'] ?? ''), 'extended') ? 'extended_parental' : 'standard_parental')
                ->multiply('52')
                ->divideBy(12);
        }

        $before = $employment['net_monthly']->add($partnerWorking);
        $during = $leaveMonthly->add($partnerDuring);
        $expenseAmount = $expenses ?? Money::zero();
        $shortfall = $expenseAmount->subtract($during);
        $months = $leaveWeeks > 0 ? max(1, (int) round($leaveWeeks * 12 / 52)) : 0;
        $totalShortfall = $shortfall->isPositive() ? $shortfall->multiply((string) $months) : Money::zero();

        return [
            'included' => true,
            'before' => $before,
            'during' => $during,
            'expenses' => $expenseAmount,
            'savings_target' => $savingsTarget ?? Money::zero(),
            'monthly_shortfall' => $shortfall,
            'total_shortfall' => $totalShortfall,
            'buffer' => $totalShortfall,
            'months' => $months,
        ];
    }

    private function partnerWorkingMonthly(Province $province, ?Money $partnerSalary): Money
    {
        if (! $partnerSalary || $partnerSalary->isZero()) {
            return Money::zero();
        }

        $payroll = $this->payroll->calculate([
            'annual_salary' => $partnerSalary->dollars(),
            'province' => $province->value,
            'frequency' => PayFrequency::Monthly->value,
        ]);

        return $payroll->metrics['net_annual']->divideBy(12);
    }

    private function optionalMoney(mixed $value): ?Money
    {
        if ($value instanceof Money) {
            return $value;
        }

        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return Money::fromDollars($value);
    }

    /**
     * @return array<string, mixed>
     */
    private function quebecResponse(Province $province, Money $salary, PayFrequency $frequency): array
    {
        return [
            'supported' => false,
            'quebec' => true,
            'province' => $province,
            'employment' => $this->employmentIncome($province, $salary, $frequency),
            'qpip_url' => config('external-links.government.qpip'),
            'warnings' => [
                'Québec maternity, paternity, parental, and adoption benefits are paid through the Québec Parental Insurance Plan (QPIP), not federal EI. This calculator does not estimate QPIP. Use the official RQAP estimator for Québec leave income.',
            ],
            'year' => $this->benefits->year(),
            'reviewed' => $this->benefits->lastReviewedLabel(),
            'sources' => $this->benefits->sources(),
        ];
    }
}
