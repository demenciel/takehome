<?php

return [
    'code' => 'ON',
    'lowest_rate' => '0.0505',
    'basic_personal_amount' => '12989.00',
    'bpa_formula' => 'fixed',
    'surtax' => [
        ['threshold' => '5818.00', 'rate' => '0.20'],
        ['threshold' => '7446.00', 'rate' => '0.36'],
    ],
    'tax_reduction' => [
        'basic' => '300.00',
    ],
    'health_premium' => [
        ['up_to' => '20000.00', 'cap' => '0.00'],
        ['up_to' => '36000.00', 'cap' => '300.00', 'base' => '0.00', 'rate' => '0.06', 'from' => '20000.00'],
        ['up_to' => '48000.00', 'cap' => '450.00', 'base' => '300.00', 'rate' => '0.06', 'from' => '36000.00'],
        ['up_to' => '72000.00', 'cap' => '600.00', 'base' => '450.00', 'rate' => '0.25', 'from' => '48000.00'],
        ['up_to' => '200000.00', 'cap' => '750.00', 'base' => '600.00', 'rate' => '0.25', 'from' => '72000.00'],
        ['up_to' => null, 'cap' => '900.00', 'base' => '750.00', 'rate' => '0.25', 'from' => '200000.00'],
    ],
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0505', 'constant' => '0.00'],
        ['threshold' => '53891.00', 'rate' => '0.0915', 'constant' => '2210.00'],
        ['threshold' => '107785.00', 'rate' => '0.1116', 'constant' => '4376.00'],
        ['threshold' => '150000.00', 'rate' => '0.1216', 'constant' => '5876.00'],
        ['threshold' => '220000.00', 'rate' => '0.1316', 'constant' => '8076.00'],
    ],
];
