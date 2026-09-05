<?php

use App\Calculators\Payroll\PayrollCalculator;

return [
    'default' => 'paycheck',

    'popular_salaries' => [
        30000,
        35000,
        40000,
        45000,
        50000,
        55000,
        60000,
        65000,
        70000,
        75000,
        80000,
        85000,
        90000,
        100000,
        110000,
        120000,
        150000,
    ],

    'example_salaries' => [
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

    /**
     * Provinces that receive indexable salary pages in this release.
     * All jurisdictions still have calculator landing pages.
     */
    'salary_page_provinces' => [
        'ON',
        'QC',
        'BC',
        'AB',
        'NB',
        'NS',
        'MB',
    ],

    'definitions' => [
        'paycheck' => [
            'key' => 'paycheck',
            'name' => 'Canadian Paycheck Calculator',
            'calculator' => PayrollCalculator::class,
            'category' => 'payroll',
            'route' => 'paycheck.canada',
        ],
    ],
];
