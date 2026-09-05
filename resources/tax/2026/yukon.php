<?php

return [
    'code' => 'YT',
    'lowest_rate' => '0.0640',
    'basic_personal_amount' => '16452.00',
    'bpa_formula' => 'yukon',
    'canada_employment_amount' => '1501.00',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0640', 'constant' => '0.00'],
        ['threshold' => '58523.00', 'rate' => '0.0900', 'constant' => '1522.00'],
        ['threshold' => '117045.00', 'rate' => '0.1090', 'constant' => '3745.00'],
        ['threshold' => '181440.00', 'rate' => '0.1280', 'constant' => '7193.00'],
        ['threshold' => '500000.00', 'rate' => '0.1500', 'constant' => '18193.00'],
    ],
];
