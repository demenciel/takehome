<?php

return [
    'code' => 'AB',
    'lowest_rate' => '0.0800',
    'basic_personal_amount' => '22769.00',
    'bpa_formula' => 'fixed',
    'alberta_tax_credit_threshold' => '4896.00',
    'alberta_tax_credit_rate' => '0.25',
    'brackets' => [
        ['threshold' => '0.00', 'rate' => '0.0800', 'constant' => '0.00'],
        ['threshold' => '61200.00', 'rate' => '0.1000', 'constant' => '1224.00'],
        ['threshold' => '154259.00', 'rate' => '0.1200', 'constant' => '4309.00'],
        ['threshold' => '185111.00', 'rate' => '0.1300', 'constant' => '6160.00'],
        ['threshold' => '246813.00', 'rate' => '0.1400', 'constant' => '8628.00'],
        ['threshold' => '370220.00', 'rate' => '0.1500', 'constant' => '12331.00'],
    ],
];
