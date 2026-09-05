<?php

use App\Calculators\Payroll\PayrollCalculator;

return [
    'default' => 'paycheck',

    'popular_salaries' => [
        40000,
        50000,
        60000,
        70000,
        80000,
        90000,
        100000,
        120000,
        150000,
    ],

    'definitions' => [
        'paycheck' => [
            'key' => 'paycheck',
            'name' => 'Canadian Paycheck Calculator',
            'calculator' => PayrollCalculator::class,
            'category' => 'payroll',
            'route' => 'paycheck.show',
            'national_slug' => 'canada-paycheck-calculator',
        ],
    ],
];
