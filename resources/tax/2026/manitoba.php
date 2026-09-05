<?php

return [
    'code' => 'MB',
    'lowest_rate' => '0.1080',
    'basic_personal_amount' => '15780.00',
    'bpa_formula' => 'manitoba',
    'bpa_phase_out_start' => '200000.00',
    'bpa_phase_out_end' => '400000.00',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.1080', 'constant' => '0.00'],
        ['threshold' => '47000.00', 'rate' => '0.1275', 'constant' => '917.00'],
        ['threshold' => '100000.00', 'rate' => '0.1740', 'constant' => '5567.00'],
    ],
];
