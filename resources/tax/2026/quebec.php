<?php

/**
 * Quebec provincial income tax — Revenu Québec 2026 parameters.
 *
 * CRA T4127 does not compute Quebec provincial tax (T2 = 0).
 */
return [
    'code' => 'QC',
    'lowest_rate' => '0.1400',
    'basic_personal_amount' => '18952.00',
    'bpa_formula' => 'fixed',
    'worker_deduction' => '1450.00',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.1400', 'constant' => '0.00'],
        ['threshold' => '54345.00', 'rate' => '0.1900', 'constant' => '2717.25'],
        ['threshold' => '108680.00', 'rate' => '0.2400', 'constant' => '8151.25'],
        ['threshold' => '132245.00', 'rate' => '0.2575', 'constant' => '10465.54'],
    ],
];
