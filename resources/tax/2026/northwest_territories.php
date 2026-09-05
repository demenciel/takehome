<?php

return [
    'code' => 'NT',
    'lowest_rate' => '0.0590',
    'basic_personal_amount' => '18198.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0590', 'constant' => '0.00'],
        ['threshold' => '53003.00', 'rate' => '0.0860', 'constant' => '1431.00'],
        ['threshold' => '106009.00', 'rate' => '0.1220', 'constant' => '5247.00'],
        ['threshold' => '172346.00', 'rate' => '0.1405', 'constant' => '8436.00'],
    ],
];
