<?php

return [
    'code' => 'NU',
    'lowest_rate' => '0.0400',
    'basic_personal_amount' => '19659.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0400', 'constant' => '0.00'],
        ['threshold' => '55801.00', 'rate' => '0.0700', 'constant' => '1674.00'],
        ['threshold' => '111602.00', 'rate' => '0.0900', 'constant' => '3906.00'],
        ['threshold' => '181439.00', 'rate' => '0.1150', 'constant' => '8442.00'],
    ],
];
