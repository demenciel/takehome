<?php

/**
 * Newfoundland and Labrador — July 1, 2026 Option 1 prorated BPA $15,000.
 */
return [
    'code' => 'NL',
    'lowest_rate' => '0.0870',
    'basic_personal_amount' => '15000.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0870', 'constant' => '0.00'],
        ['threshold' => '44678.00', 'rate' => '0.1450', 'constant' => '2591.00'],
        ['threshold' => '89354.00', 'rate' => '0.1580', 'constant' => '3753.00'],
        ['threshold' => '159528.00', 'rate' => '0.1780', 'constant' => '6943.00'],
        ['threshold' => '223340.00', 'rate' => '0.1980', 'constant' => '11410.00'],
        ['threshold' => '285319.00', 'rate' => '0.2080', 'constant' => '14263.00'],
        ['threshold' => '570638.00', 'rate' => '0.2130', 'constant' => '17117.00'],
        ['threshold' => '1141275.00', 'rate' => '0.2180', 'constant' => '22823.00'],
    ],
];
