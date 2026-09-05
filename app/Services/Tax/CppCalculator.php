<?php

namespace App\Services\Tax;

use App\Support\Money;

class CppCalculator
{
    /**
     * @param  array<string, mixed>  $rules
     * @return array{contribution: Money, second_additional: Money, base: Money, additional: Money}
     */
    public function annual(Money $pensionableEarnings, array $rules): array
    {
        $exemption = Money::fromDollars($rules['basic_exemption']);
        $ympe = Money::fromDollars($rules['ympe']);
        $yampe = Money::fromDollars($rules['yampe']);
        $rate = (string) $rules['employee_rate'];
        $baseRate = (string) $rules['base_rate'];
        $maximum = Money::fromDollars($rules['maximum_employee']);
        $maximumBase = Money::fromDollars($rules['maximum_base_employee']);
        $maximumSecond = Money::fromDollars($rules['maximum_second_additional']);
        $secondRate = (string) $rules['second_additional_rate'];

        $firstBand = $pensionableEarnings->min($ympe)->subtract($exemption);
        $contribution = $firstBand->isNegative()
            ? Money::zero()
            : $firstBand->multiply($rate)->min($maximum);

        $secondBand = $pensionableEarnings->min($yampe)->subtract($ympe);
        $second = $secondBand->isNegative()
            ? Money::zero()
            : $secondBand->multiply($secondRate)->min($maximumSecond);

        $base = $contribution->multiply(bcdiv($baseRate, $rate, 10))->min($maximumBase);
        $additional = $contribution->subtract($base);

        return [
            'contribution' => $contribution,
            'second_additional' => $second,
            'base' => $base,
            'additional' => $additional,
        ];
    }
}
