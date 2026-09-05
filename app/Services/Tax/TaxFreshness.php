<?php

namespace App\Services\Tax;

use Carbon\CarbonImmutable;

final class TaxFreshness
{
    public function __construct(private TaxRuleProvider $provider) {}

    public function year(): int
    {
        return $this->provider->currentYear();
    }

    public function lastUpdated(): CarbonImmutable
    {
        $sources = $this->provider->forYear()->sources;
        $date = $sources['retrieved_date'] ?? $sources['effective_date'] ?? null;

        return $date
            ? CarbonImmutable::parse((string) $date)
            : CarbonImmutable::create($this->year(), 1, 1);
    }

    public function lastUpdatedLabel(): string
    {
        return $this->lastUpdated()->format('F j, Y');
    }

    public function attribution(): string
    {
        return 'Based on '.$this->year().' Canadian payroll tax rates and contribution rules.';
    }

    public function edition(): ?string
    {
        return $this->provider->forYear()->sources['edition'] ?? null;
    }

    /**
     * @return list<array{title: string, url: string}>
     */
    public function sources(): array
    {
        return $this->provider->forYear()->sources['sources'] ?? [];
    }
}
