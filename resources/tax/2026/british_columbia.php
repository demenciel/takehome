<?php

/**
 * British Columbia — July 1, 2026 Option 1 (prorated lowest rate and tax reduction).
 *
 * Source: CRA T4127 123rd edition Table 8.1 / 8.2.
 */
return [
    'code' => 'BC',
    'lowest_rate' => '0.0614',
    'basic_personal_amount' => '13216.00',
    'bpa_formula' => 'fixed',
    'tax_reduction' => [
        'basic' => '805.00',
        'full_threshold' => '25570.00',
        'phase_out_end' => '44952.00',
        'phase_out_rate' => '0.0356',
    ],
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0614', 'constant' => '0.00'],
        ['threshold' => '50363.00', 'rate' => '0.0770', 'constant' => '786.00'],
        ['threshold' => '100728.00', 'rate' => '0.1050', 'constant' => '3606.00'],
        ['threshold' => '115648.00', 'rate' => '0.1229', 'constant' => '5676.00'],
        ['threshold' => '140430.00', 'rate' => '0.1470', 'constant' => '9061.00'],
        ['threshold' => '190405.00', 'rate' => '0.1680', 'constant' => '13059.00'],
        ['threshold' => '265545.00', 'rate' => '0.2050', 'constant' => '22884.00'],
    ],
];
