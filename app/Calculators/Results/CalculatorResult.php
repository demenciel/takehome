<?php

namespace App\Calculators\Results;

use App\Support\Money;

final class CalculatorResult
{
    /**
     * @param  array<string, mixed>  $inputs
     * @param  list<array{key: string, label: string, amount: Money, kind: string}>  $periodBreakdown
     * @param  list<array{key: string, label: string, amount: Money, kind: string}>  $annualBreakdown
     * @param  array<string, mixed>  $metrics
     * @param  list<string>  $warnings
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $summary,
        public readonly Money $headlineAmount,
        public readonly string $headlinePeriod,
        public readonly array $inputs,
        public readonly array $periodBreakdown,
        public readonly array $annualBreakdown,
        public readonly array $metrics,
        public readonly array $warnings,
        public readonly array $metadata,
        public readonly string $shareText,
        public readonly string $copyText,
    ) {}
}
