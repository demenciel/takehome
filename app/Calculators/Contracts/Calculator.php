<?php

namespace App\Calculators\Contracts;

use App\Calculators\Results\CalculatorResult;

interface Calculator
{
    public function key(): string;

    /**
     * @param  array<string, mixed>  $inputs
     */
    public function calculate(array $inputs): CalculatorResult;
}
