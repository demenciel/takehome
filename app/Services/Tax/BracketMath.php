<?php

namespace App\Services\Tax;

use App\Support\Money;

final class BracketMath
{
    /**
     * CRA constant method: tax = (R × A) − K.
     *
     * @param  list<array{threshold: string, rate: string, constant: string}>  $brackets
     * @return array{tax: Money, rate: string, constant: Money, threshold: string}
     */
    public static function apply(Money $income, array $brackets): array
    {
        $selected = $brackets[0];

        foreach ($brackets as $bracket) {
            $threshold = Money::fromDollars($bracket['threshold']);

            if ($income->greaterThan($threshold) || $income->equals($threshold)) {
                $selected = $bracket;
            }
        }

        $tax = $income->multiply($selected['rate'])->subtract(Money::fromDollars($selected['constant']));

        if ($tax->isNegative()) {
            $tax = Money::zero();
        }

        return [
            'tax' => $tax,
            'rate' => $selected['rate'],
            'constant' => Money::fromDollars($selected['constant']),
            'threshold' => $selected['threshold'],
        ];
    }
}
