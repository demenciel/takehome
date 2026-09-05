<?php

return [
    'code' => 'SK',
    'lowest_rate' => '0.1050',
    'basic_personal_amount' => '20381.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.1050', 'constant' => '0.00'],
        ['threshold' => '54532.00', 'rate' => '0.1250', 'constant' => '1091.00'],
        ['threshold' => '155805.00', 'rate' => '0.1450', 'constant' => '4207.00'],
    ],
];
