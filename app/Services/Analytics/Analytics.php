<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;
use App\Support\SalaryRange;
use Illuminate\Support\Facades\Log;
use Throwable;

class Analytics
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(string $name, array $payload = []): void
    {
        if (! config('analytics.enabled')) {
            return;
        }

        try {
            AnalyticsEvent::query()->create([
                'name' => $name,
                'tool_key' => $payload['tool_key'] ?? null,
                'province' => $payload['province'] ?? null,
                'frequency' => $payload['frequency'] ?? null,
                'salary_range' => $payload['salary_range'] ?? (isset($payload['annual_salary_cents'])
                    ? SalaryRange::bucket((int) $payload['annual_salary_cents'])
                    : null),
                'path' => $payload['path'] ?? null,
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Analytics event could not be stored.', [
                'name' => $name,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
