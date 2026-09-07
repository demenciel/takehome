<?php

namespace App\Support;

use App\Services\Analytics\Analytics;

trait FamilyFunnel
{
    public function trackFunnel(string $name, ?string $context = null): void
    {
        app(Analytics::class)->record($name, [
            'tool_key' => $this->toolKey(),
            'province' => $this->province ?? null,
            'frequency' => $context ?? $this->funnelContext(),
            'path' => request()->path(),
        ]);
    }

    abstract protected function toolKey(): string;

    protected function funnelContext(): ?string
    {
        return null;
    }
}
