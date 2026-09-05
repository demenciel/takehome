<?php

namespace App\Services\Tax;

use App\Support\Money;

class QppCalculator
{
    public function __construct(private CppCalculator $cppCalculator) {}

    /**
     * @param  array<string, mixed>  $rules
     * @return array{contribution: Money, second_additional: Money, base: Money, additional: Money}
     */
    public function annual(Money $pensionableEarnings, array $rules): array
    {
        return $this->cppCalculator->annual($pensionableEarnings, $rules);
    }
}
