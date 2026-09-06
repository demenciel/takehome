<?php

/**
 * Reserve Force Class A and Class B daily rates (standard occupation group).
 * Source: National Defence, Reserve Force pay by rank, effective 1 April 2025.
 * Amounts are daily dollars as published. Do not derive these from monthly rates.
 */
return [
    'unit' => 'daily',
    'ranks' => [
        'private' => [
            'name' => 'Private / Aviator / Sailor 2nd or 3rd Class',
            'short' => 'Private',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['1' => '143.76', '2' => '153.80', '3' => '184.86'],
            ],
        ],
        'corporal' => [
            'name' => 'Corporal / Sailor 1st Class',
            'short' => 'Corporal',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '209.28', '1' => '212.92', '2' => '216.56', '3' => '220.10', '4' => '223.92'],
            ],
        ],
        'master-corporal' => [
            'name' => 'Master Corporal / Master Sailor',
            'short' => 'Master Corporal',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '217.22', '1' => '221.04', '2' => '224.68', '3' => '228.32', '4' => '239.26'],
            ],
        ],
        'sergeant' => [
            'name' => 'Sergeant / Petty Officer 2nd Class',
            'short' => 'Sergeant',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '242.86', '1' => '246.40', '2' => '249.80', '3' => '252.26', '4' => '254.48'],
            ],
        ],
        'warrant-officer' => [
            'name' => 'Warrant Officer / Petty Officer 1st Class',
            'short' => 'Warrant Officer',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '265.30', '1' => '267.62', '2' => '270.10', '3' => '272.46', '4' => '274.56'],
            ],
        ],
        'master-warrant-officer' => [
            'name' => 'Master Warrant Officer / Chief Petty Officer 2nd Class',
            'short' => 'Master Warrant Officer',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '295.00', '1' => '298.02', '2' => '300.98', '3' => '303.96', '4' => '306.86'],
            ],
        ],
        'chief-warrant-officer' => [
            'name' => 'Chief Warrant Officer / Chief Petty Officer 1st Class',
            'short' => 'Chief Warrant Officer',
            'group' => 'ncm',
            'levels' => [
                'A' => ['basic' => '322.30', '1' => '325.62', '2' => '329.14', '3' => '332.42', '4' => '335.66'],
                'B' => ['basic' => '344.82', '1' => '348.46', '2' => '352.16', '3' => '355.68', '4' => '359.24'],
                'C' => ['basic' => '358.64', '1' => '362.42', '2' => '366.30', '3' => '369.84', '4' => '373.54'],
            ],
        ],
        'officer-cadet' => [
            'name' => 'Officer Cadet / Naval Cadet',
            'short' => 'Officer Cadet',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '150.48', '1' => '156.68', '2' => '162.96', '3' => '180.98'],
            ],
        ],
        'second-lieutenant' => [
            'name' => 'Second Lieutenant / Acting Sub-Lieutenant',
            'short' => 'Second Lieutenant',
            'group' => 'officer',
            'levels' => [
                'A' => ['basic' => '168.88', '1' => '183.18', '2' => '203.28', '3' => '212.16'],
                'B' => ['basic' => '215.62', '1' => '221.92', '2' => '234.26', '3' => '240.42', '4' => '247.18', '5' => '249.82', '6' => '257.38', '7' => '265.00', '8' => '272.98', '9' => '281.18', '10' => '289.56'],
                'C' => ['basic' => '218.12', '1' => '224.62', '2' => '236.06', '3' => '238.30', '4' => '245.54', '5' => '252.84', '6' => '260.42', '7' => '268.20', '8' => '276.22', '9' => '284.64', '10' => '292.90'],
            ],
        ],
        'lieutenant' => [
            'name' => 'Lieutenant / Sub-Lieutenant',
            'short' => 'Lieutenant',
            'group' => 'officer',
            'levels' => [
                'A' => ['basic' => '210.68', '1' => '217.10', '2' => '223.84', '3' => '234.44', '4' => '235.80'],
                'B' => ['basic' => '221.38', '1' => '234.78', '2' => '239.32', '3' => '249.00', '4' => '258.92', '5' => '269.34', '6' => '280.02', '7' => '291.26', '8' => '302.92', '9' => '314.94', '10' => '327.66'],
                'C' => ['basic' => '229.92', '1' => '239.14', '2' => '248.72', '3' => '258.58', '4' => '269.06', '5' => '279.82', '6' => '291.06', '7' => '302.76', '8' => '314.74', '9' => '327.40', '10' => '340.46'],
            ],
        ],
        'captain' => [
            'name' => 'Captain / Lieutenant (Navy)',
            'short' => 'Captain',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '270.34', '1' => '280.60', '2' => '290.94', '3' => '301.16', '4' => '311.14', '5' => '320.76', '6' => '330.32', '7' => '340.12', '8' => '345.84', '9' => '351.48', '10' => '357.36'],
            ],
        ],
        'major' => [
            'name' => 'Major / Lieutenant-Commander',
            'short' => 'Major',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '365.60', '1' => '372.00', '2' => '378.34', '3' => '384.68', '4' => '390.98', '5' => '397.30', '6' => '403.64', '7' => '409.92'],
            ],
        ],
        'lieutenant-colonel' => [
            'name' => 'Lieutenant-Colonel / Commander',
            'short' => 'Lieutenant-Colonel',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '423.66', '1' => '430.56', '2' => '437.16', '3' => '444.10', '4' => '450.92'],
            ],
        ],
        'colonel' => [
            'name' => 'Colonel / Captain (Navy)',
            'short' => 'Colonel',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '478.54', '1' => '497.42', '2' => '516.32', '3' => '535.20'],
            ],
        ],
        'brigadier-general' => [
            'name' => 'Brigadier-General / Commodore',
            'short' => 'Brigadier-General',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '566.42', '1' => '581.52', '2' => '597.56', '3' => '613.16'],
            ],
        ],
        'major-general' => [
            'name' => 'Major-General / Rear-Admiral',
            'short' => 'Major-General',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '649.92', '1' => '687.20', '2' => '725.86', '3' => '763.38'],
            ],
        ],
        'lieutenant-general' => [
            'name' => 'Lieutenant-General / Vice-Admiral',
            'short' => 'Lieutenant-General',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '834.78', '1' => '857.38', '2' => '881.12', '3' => '903.68'],
            ],
        ],
    ],
];
