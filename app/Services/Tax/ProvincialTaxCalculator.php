<?php

namespace App\Services\Tax;

use App\Support\Money;
use App\Support\Province;

class ProvincialTaxCalculator
{
    public function __construct(private FederalTaxCalculator $federalTaxCalculator) {}

    /**
     * @param  array<string, mixed>  $provinceRules
     * @param  array<string, mixed>  $federal
     * @return array{t2: Money, t4: Money, k1p: Money, k2p: Money, surtax: Money, health_premium: Money, reduction: Money, bpa: Money}
     */
    public function annual(
        Money $taxableIncome,
        Money $employmentIncome,
        Money $basePension,
        Money $employmentInsurance,
        Money $qpip,
        Province $province,
        array $provinceRules,
        array $federal,
    ): array {
        if ($province->usesQpp()) {
            return $this->quebec(
                $taxableIncome,
                $basePension,
                $employmentInsurance,
                $qpip,
                $provinceRules,
            );
        }

        $lowest = (string) $provinceRules['lowest_rate'];
        $bpa = $this->basicPersonalAmount($employmentIncome, $provinceRules, $federal);
        $k1p = $bpa->multiply($lowest);
        $k2p = $basePension->add($employmentInsurance)->multiply($lowest);

        $bracket = BracketMath::apply($taxableIncome, $provinceRules['brackets']);
        $t4 = $bracket['tax']->subtract($k1p)->subtract($k2p);

        $k4p = Money::zero();
        $k5p = Money::zero();

        if ($province === Province::Yukon) {
            $cea = Money::fromDollars($provinceRules['canada_employment_amount'] ?? $federal['canada_employment_amount']);
            $k4p = $employmentIncome->min($cea)->multiply($lowest);
            $t4 = $t4->subtract($k4p);
        }

        if ($province === Province::Alberta) {
            $threshold = Money::fromDollars($provinceRules['alberta_tax_credit_threshold']);
            $excess = $k1p->add($k2p)->subtract($threshold);
            $k5p = $excess->isNegative()
                ? Money::zero()
                : $excess->multiply((string) $provinceRules['alberta_tax_credit_rate']);
            $t4 = $t4->subtract($k5p);
        }

        if ($t4->isNegative()) {
            $t4 = Money::zero();
        }

        $surtax = $this->ontarioSurtax($t4, $provinceRules);
        $health = $this->ontarioHealthPremium($taxableIncome, $provinceRules);
        $reduction = $this->taxReduction($taxableIncome, $t4, $surtax, $province, $provinceRules);

        $t2 = $t4->add($surtax)->add($health)->subtract($reduction);

        if ($t2->isNegative()) {
            $t2 = Money::zero();
        }

        return [
            't2' => $t2,
            't4' => $t4,
            'k1p' => $k1p,
            'k2p' => $k2p,
            'surtax' => $surtax,
            'health_premium' => $health,
            'reduction' => $reduction,
            'bpa' => $bpa,
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<string, mixed>  $federal
     */
    public function basicPersonalAmount(Money $netIncome, array $rules, array $federal): Money
    {
        $formula = $rules['bpa_formula'] ?? 'fixed';

        if ($formula === 'yukon') {
            return $this->federalTaxCalculator->basicPersonalAmount($netIncome, $federal);
        }

        if ($formula === 'manitoba') {
            $max = Money::fromDollars($rules['basic_personal_amount']);
            $start = Money::fromDollars($rules['bpa_phase_out_start']);
            $end = Money::fromDollars($rules['bpa_phase_out_end']);

            if ($netIncome->lessThanOrEqual($start)) {
                return $max;
            }

            if ($netIncome->greaterThanOrEqual($end)) {
                return Money::zero();
            }

            $over = $netIncome->subtract($start);
            $span = $end->subtract($start);
            $ratio = bcdiv((string) $max->cents, (string) $span->cents, 12);

            $amount = $max->subtract(Money::fromRateProduct($over->cents, $ratio));

            return $amount->isNegative() ? Money::zero() : $amount;
        }

        return Money::fromDollars($rules['basic_personal_amount']);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array{t2: Money, t4: Money, k1p: Money, k2p: Money, surtax: Money, health_premium: Money, reduction: Money, bpa: Money}
     */
    private function quebec(
        Money $taxableIncome,
        Money $basePension,
        Money $employmentInsurance,
        Money $qpip,
        array $rules,
    ): array {
        $lowest = (string) $rules['lowest_rate'];
        $bpa = Money::fromDollars($rules['basic_personal_amount']);
        $k1p = $bpa->multiply($lowest);
        $k2p = $basePension->add($employmentInsurance)->add($qpip)->multiply($lowest);

        $bracket = BracketMath::apply($taxableIncome, $rules['brackets']);
        $t4 = $bracket['tax']->subtract($k1p)->subtract($k2p);

        if ($t4->isNegative()) {
            $t4 = Money::zero();
        }

        return [
            't2' => $t4,
            't4' => $t4,
            'k1p' => $k1p,
            'k2p' => $k2p,
            'surtax' => Money::zero(),
            'health_premium' => Money::zero(),
            'reduction' => Money::zero(),
            'bpa' => $bpa,
        ];
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    private function ontarioSurtax(Money $t4, array $rules): Money
    {
        if (! isset($rules['surtax'])) {
            return Money::zero();
        }

        $total = Money::zero();

        foreach ($rules['surtax'] as $band) {
            $threshold = Money::fromDollars($band['threshold']);

            if ($t4->greaterThan($threshold)) {
                $total = $total->add($t4->subtract($threshold)->multiply((string) $band['rate']));
            }
        }

        return $total;
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    private function ontarioHealthPremium(Money $taxableIncome, array $rules): Money
    {
        if (! isset($rules['health_premium'])) {
            return Money::zero();
        }

        foreach ($rules['health_premium'] as $band) {
            $upTo = $band['up_to'] === null ? null : Money::fromDollars($band['up_to']);

            if ($upTo instanceof Money && $taxableIncome->greaterThan($upTo)) {
                continue;
            }

            $cap = Money::fromDollars($band['cap']);

            if (! isset($band['rate'])) {
                return $cap;
            }

            $from = Money::fromDollars($band['from']);
            $base = Money::fromDollars($band['base']);
            $calculated = $base->add($taxableIncome->subtract($from)->multiply((string) $band['rate']));

            return $calculated->min($cap);
        }

        return Money::zero();
    }

    /**
     * @param  array<string, mixed>  $rules
     */
    private function taxReduction(Money $taxableIncome, Money $t4, Money $surtax, Province $province, array $rules): Money
    {
        if (! isset($rules['tax_reduction'])) {
            return Money::zero();
        }

        if ($province === Province::Ontario) {
            $basic = Money::fromDollars($rules['tax_reduction']['basic']);
            $combined = $t4->add($surtax);
            $reduction = $basic->multiply('2')->subtract($combined);

            if ($reduction->isNegative()) {
                return Money::zero();
            }

            return $reduction->min($combined);
        }

        if ($province === Province::BritishColumbia) {
            $basic = Money::fromDollars($rules['tax_reduction']['basic']);
            $fullUntil = Money::fromDollars($rules['tax_reduction']['full_threshold']);
            $phaseOutEnd = Money::fromDollars($rules['tax_reduction']['phase_out_end']);
            $phaseOutRate = (string) $rules['tax_reduction']['phase_out_rate'];

            if ($taxableIncome->lessThanOrEqual($fullUntil)) {
                return $t4->min($basic);
            }

            if ($taxableIncome->greaterThan($phaseOutEnd)) {
                return Money::zero();
            }

            $phased = $basic->subtract($taxableIncome->subtract($fullUntil)->multiply($phaseOutRate));

            if ($phased->isNegative()) {
                return Money::zero();
            }

            return $t4->min($phased);
        }

        return Money::zero();
    }
}
