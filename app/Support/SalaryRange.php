<?php

namespace App\Support;

final class SalaryRange
{
    /**
     * Bucket an annual salary for analytics. Never store the exact amount.
     */
    public static function bucket(int $annualCents): string
    {
        $dollars = intdiv(abs($annualCents), 100);

        return match (true) {
            $dollars < 30000 => 'under_30k',
            $dollars < 40000 => '30_40k',
            $dollars < 50000 => '40_50k',
            $dollars < 60000 => '50_60k',
            $dollars < 70000 => '60_70k',
            $dollars < 80000 => '70_80k',
            $dollars < 90000 => '80_90k',
            $dollars < 100000 => '90_100k',
            $dollars < 120000 => '100_120k',
            $dollars < 150000 => '120_150k',
            $dollars < 200000 => '150_200k',
            default => '200k_plus',
        };
    }
}
