<?php

/**
 * Prince Edward Island — July 1, 2026 Option 1 (prorated 21% top rate).
 */
return [
    'code' => 'PE',
    'lowest_rate' => '0.0950',
    'basic_personal_amount' => '15000.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0950', 'constant' => '0.00'],
        ['threshold' => '33928.00', 'rate' => '0.1347', 'constant' => '1347.00'],
        ['threshold' => '65820.00', 'rate' => '0.1660', 'constant' => '3407.00'],
        ['threshold' => '106890.00', 'rate' => '0.1762', 'constant' => '4497.00'],
        ['threshold' => '142520.00', 'rate' => '0.1900', 'constant' => '6464.00'],
        ['threshold' => '200000.00', 'rate' => '0.2100', 'constant' => '10464.00'],
    ],
];
