<?php

namespace App\Services\Tax;

use App\Support\Money;
use App\Support\Province;

class EiCalculator
{
    /**
     * @param  array<string, mixed>  $rules
     */
    public function annual(Money $insurableEarnings, Province $province, array $rules): Money
    {
        $rate = $province->usesQpp()
            ? (string) $rules['quebec_employee_rate']
            : (string) $rules['employee_rate'];

        $maximum = $province->usesQpp()
            ? Money::fromDollars($rules['quebec_maximum_employee'])
            : Money::fromDollars($rules['maximum_employee']);

        $cap = Money::fromDollars($rules['maximum_insurable_earnings']);

        return $insurableEarnings->min($cap)->multiply($rate)->min($maximum);
    }
}
