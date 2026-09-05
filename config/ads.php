<?php

return [
    'enabled' => (bool) env('ADS_ENABLED', false),
    'provider' => env('ADS_PROVIDER', ''),
    'client' => env('ADS_CLIENT', ''),

    'slots' => [
        'top' => [
            'enabled' => true,
            'format' => 'horizontal',
        ],
        'middle' => [
            'enabled' => true,
            'format' => 'rectangle',
        ],
        'bottom' => [
            'enabled' => true,
            'format' => 'horizontal',
        ],
        'result-inline' => [
            'enabled' => true,
            'format' => 'in-article',
        ],
        'content-mid' => [
            'enabled' => true,
            'format' => 'rectangle',
        ],
        'bottom-banner' => [
            'enabled' => true,
            'format' => 'horizontal',
        ],
        'sidebar' => [
            'enabled' => true,
            'format' => 'vertical',
        ],
    ],
];
