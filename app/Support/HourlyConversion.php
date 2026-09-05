<?php

namespace App\Support;

final class HourlyConversion
{
    public static function hoursPerYear(): int
    {
        return (int) config('tax.hours_per_week', 40) * (int) config('tax.weeks_per_year', 52);
    }

    public static function annualFromHourly(Money $hourly, float $hoursPerWeek = 40.0): Money
    {
        $weekly = Money::fromRateProduct($hourly->cents, (string) $hoursPerWeek);

        return $weekly->multiply((string) config('tax.weeks_per_year', 52));
    }

    public static function hourlyFromAnnual(Money $annual, float $hoursPerWeek = 40.0): Money
    {
        $weeks = (int) config('tax.weeks_per_year', 52);
        $hours = $hoursPerWeek * $weeks;

        if ($hours <= 0) {
            return Money::zero();
        }

        return $annual->divideBy((int) round($hours));
    }

    public static function assumptionLabel(): string
    {
        $hours = (int) config('tax.hours_per_week', 40);
        $weeks = (int) config('tax.weeks_per_year', 52);

        return "{$hours} hours/week × {$weeks} weeks (".self::hoursPerYear().' hours/year)';
    }
}
