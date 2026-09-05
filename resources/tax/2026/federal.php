<?php

/**
 * Federal payroll tax rules for 2026.
 *
 * Source: CRA T4127 Table 8.1 / T4032-OC Chart 1, effective 2026.
 * Amounts are CAD dollars as strings for auditability.
 */
return [
    'lowest_rate' => '0.1400',
    'canada_employment_amount' => '1501.00',
    'basic_personal_amount_max' => '16452.00',
    'basic_personal_amount_min' => '14829.00',
    'bpa_phase_out_start' => '181440.00',
    'bpa_phase_out_end' => '258482.00',
    'bpa_additional_amount' => '1623.00',
    'quebec_abatement' => '0.165',
    'labour_sponsored_rate' => '0.150',
    'labour_sponsored_max' => '750.00',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.1400', 'constant' => '0.00'],
        ['threshold' => '58523.00', 'rate' => '0.2050', 'constant' => '3804.00'],
        ['threshold' => '117045.00', 'rate' => '0.2600', 'constant' => '10241.00'],
        ['threshold' => '181440.00', 'rate' => '0.2900', 'constant' => '15685.00'],
        ['threshold' => '258482.00', 'rate' => '0.3300', 'constant' => '26024.00'],
    ],
];
