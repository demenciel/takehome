<?php

use App\Support\Money;

if (! function_exists('money')) {
    function money(int|string|Money $amount): Money
    {
        if ($amount instanceof Money) {
            return $amount;
        }

        if (is_int($amount)) {
            return Money::ofCents($amount);
        }

        return Money::fromDollars($amount);
    }
}

if (! function_exists('format_money')) {
    function format_money(int|string|Money $amount, string $currency = 'CAD'): string
    {
        return money($amount)->format($currency);
    }
}
