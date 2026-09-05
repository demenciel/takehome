<?php

namespace App\Services\Tax;

use App\Support\Money;
use App\Support\Province;

class QpipCalculator
{
    /**
     * @param  array<string, mixed>  $rules
     */
    public function annual(Money $insurableEarnings, Province $province, array $rules): Money
    {
        if (! $province->usesQpp()) {
            return Money::zero();
        }

        $cap = Money::fromDollars($rules['maximum_insurable_earnings']);
        $maximum = Money::fromDollars($rules['maximum_employee']);

        return $insurableEarnings->min($cap)->multiply((string) $rules['employee_rate'])->min($maximum);
    }
}
