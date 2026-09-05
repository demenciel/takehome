<?php

return [
    'enabled' => (bool) env('ANALYTICS_ENABLED', true),
    'provider' => env('ANALYTICS_PROVIDER', 'local'),
    'measurement_id' => env('ANALYTICS_MEASUREMENT_ID', ''),
];
