<?php

return [
    'current_year' => (int) env('CURRENT_TAX_YEAR', 2026),
    'currency' => env('DEFAULT_CURRENCY', 'CAD'),
    'path' => resource_path('tax'),
    'cache_ttl' => (int) env('TAX_RULES_CACHE_TTL', 86400),
    'hours_per_week' => 40,
    'weeks_per_year' => 52,
];
