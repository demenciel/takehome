<?php

namespace App\Services\Tax;

use App\Support\Money;
use App\Support\Province;

class FederalTaxCalculator
{
    /**
     * @param  array<string, mixed>  $federal
     */
    public function basicPersonalAmount(Money $netIncome, array $federal): Money
    {
        $max = Money::fromDollars($federal['basic_personal_amount_max']);
        $min = Money::fromDollars($federal['basic_personal_amount_min']);
        $start = Money::fromDollars($federal['bpa_phase_out_start']);
        $end = Money::fromDollars($federal['bpa_phase_out_end']);

        if ($netIncome->lessThanOrEqual($start)) {
            return $max;
        }

        if ($netIncome->greaterThanOrEqual($end)) {
            return $min;
        }

        $additional = Money::fromDollars($federal['bpa_additional_amount']);
        $span = $end->subtract($start);
        $over = $netIncome->subtract($start);
        $ratio = bcdiv((string) $additional->cents, (string) $span->cents, 12);
        $reduction = Money::fromRateProduct($over->cents, $ratio);

        $amount = $max->subtract($reduction);

        return $amount->lessThan($min) ? $min : $amount;
    }

    /**
     * CRA Option 1 factors T3 and T1 for a full-year employee.
     *
     * @param  array<string, mixed>  $federal
     * @return array{t1: Money, t3: Money, k1: Money, k2: Money, k4: Money, bpa: Money, bracket_rate: string}
     */
    public function annual(
        Money $taxableIncome,
        Money $employmentIncome,
        Money $basePension,
        Money $employmentInsurance,
        Money $qpip,
        Province $province,
        array $federal,
    ): array {
        $lowest = (string) $federal['lowest_rate'];
        $bpa = $this->basicPersonalAmount($employmentIncome, $federal);
        $k1 = $bpa->multiply($lowest);

        $pensionAndPremiums = $basePension->add($employmentInsurance)->add($qpip);
        $k2 = $pensionAndPremiums->multiply($lowest);

        $cea = Money::fromDollars($federal['canada_employment_amount'])->min($employmentIncome);
        $k4 = $cea->multiply($lowest);

        $bracket = BracketMath::apply($taxableIncome, $federal['brackets']);
        $t3 = $bracket['tax']->subtract($k1)->subtract($k2)->subtract($k4);

        if ($t3->isNegative()) {
            $t3 = Money::zero();
        }

        $t1 = $t3;

        if ($province->usesQpp()) {
            $t1 = $t3->subtract($t3->multiply((string) $federal['quebec_abatement']));

            if ($t1->isNegative()) {
                $t1 = Money::zero();
            }
        }

        return [
            't1' => $t1,
            't3' => $t3,
            'k1' => $k1,
            'k2' => $k2,
            'k4' => $k4,
            'bpa' => $bpa,
            'bracket_rate' => $bracket['rate'],
        ];
    }
}
