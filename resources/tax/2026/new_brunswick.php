<?php

return [
    'code' => 'NB',
    'lowest_rate' => '0.0940',
    'basic_personal_amount' => '13664.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0940', 'constant' => '0.00'],
        ['threshold' => '52333.00', 'rate' => '0.1400', 'constant' => '2407.00'],
        ['threshold' => '104666.00', 'rate' => '0.1600', 'constant' => '4501.00'],
        ['threshold' => '193861.00', 'rate' => '0.1950', 'constant' => '11286.00'],
    ],
];
