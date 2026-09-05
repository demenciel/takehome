<?php

return [
    'code' => 'NS',
    'lowest_rate' => '0.0879',
    'basic_personal_amount' => '11932.00',
    'bpa_formula' => 'fixed',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0879', 'constant' => '0.00'],
        ['threshold' => '30995.00', 'rate' => '0.1495', 'constant' => '1909.00'],
        ['threshold' => '61991.00', 'rate' => '0.1667', 'constant' => '2976.00'],
        ['threshold' => '97417.00', 'rate' => '0.1750', 'constant' => '3784.00'],
        ['threshold' => '157124.00', 'rate' => '0.2100', 'constant' => '9283.00'],
    ],
];
