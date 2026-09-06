<?php

/**
 * Regular Force and Reserve Class C monthly rates (standard occupation group).
 * Source: National Defence, Regular Force pay by rank, effective 1 April 2025.
 * Amounts are monthly dollars as published. Specialist / SOF / SAR / pilot /
 * legal / medical / dental tables are intentionally omitted.
 */
return [
    'unit' => 'monthly',
    'ranks' => [
        'private' => [
            'name' => 'Private / Aviator / Sailor 2nd or 3rd Class',
            'short' => 'Private',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['1' => '4337', '2' => '4987', '3' => '5994'],
            ],
        ],
        'corporal' => [
            'name' => 'Corporal / Sailor 1st Class',
            'short' => 'Corporal',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '6858', '1' => '6978', '2' => '7095', '3' => '7213', '4' => '7337'],
            ],
        ],
        'master-corporal' => [
            'name' => 'Master Corporal / Master Sailor',
            'short' => 'Master Corporal',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '7118', '1' => '7242', '2' => '7363', '3' => '7482', '4' => '7841'],
            ],
        ],
        'sergeant' => [
            'name' => 'Sergeant / Petty Officer 2nd Class',
            'short' => 'Sergeant',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '7959', '1' => '8076', '2' => '8187', '3' => '8266', '4' => '8340'],
            ],
        ],
        'warrant-officer' => [
            'name' => 'Warrant Officer / Petty Officer 1st Class',
            'short' => 'Warrant Officer',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '8694', '1' => '8770', '2' => '8850', '3' => '8927', '4' => '8997'],
            ],
        ],
        'master-warrant-officer' => [
            'name' => 'Master Warrant Officer / Chief Petty Officer 2nd Class',
            'short' => 'Master Warrant Officer',
            'group' => 'ncm',
            'levels' => [
                'standard' => ['basic' => '9668', '1' => '9767', '2' => '9865', '3' => '9961', '4' => '10057'],
            ],
        ],
        'chief-warrant-officer' => [
            'name' => 'Chief Warrant Officer / Chief Petty Officer 1st Class',
            'short' => 'Chief Warrant Officer',
            'group' => 'ncm',
            'levels' => [
                'A' => ['basic' => '10562', '1' => '10671', '2' => '10788', '3' => '10896', '4' => '11001'],
                'B' => ['basic' => '11300', '1' => '11420', '2' => '11542', '3' => '11656', '4' => '11775'],
                'C' => ['basic' => '12920', '1' => '13045', '2' => '13171', '3' => '13290', '4' => '13410'],
            ],
        ],
        'officer-cadet' => [
            'name' => 'Officer Cadet / Naval Cadet',
            'short' => 'Officer Cadet',
            'group' => 'officer',
            'levels' => [
                'A' => ['basic' => '2913', '1' => '2965', '2' => '3033', '3' => '3089'],
                'B' => ['basic' => '4044', '1' => '4212', '2' => '4872', '3' => '5060'],
            ],
        ],
        'second-lieutenant' => [
            'name' => 'Second Lieutenant / Acting Sub-Lieutenant',
            'short' => 'Second Lieutenant',
            'group' => 'officer',
            'levels' => [
                'A' => ['basic' => '6413', '1' => '6507'],
                'B' => ['basic' => '5096', '1' => '5397', '2' => '5910', '3' => '6433'],
                'C' => ['basic' => '5484', '1' => '5948', '2' => '6471', '3' => '6890', '4' => '7361', '5' => '7833', '6' => '8305'],
                'D' => ['basic' => '7001', '1' => '7206', '2' => '7423', '3' => '7719', '4' => '7881', '5' => '8114', '6' => '8358', '7' => '8606', '8' => '8864', '9' => '9129', '10' => '9402'],
                'E' => ['basic' => '7084', '1' => '7294', '2' => '7585', '3' => '7874', '4' => '7989', '5' => '8211', '6' => '8457', '7' => '8710', '8' => '8970', '9' => '9244', '10' => '9514'],
            ],
        ],
        'lieutenant' => [
            'name' => 'Lieutenant / Sub-Lieutenant',
            'short' => 'Lieutenant',
            'group' => 'officer',
            'levels' => [
                'A' => ['basic' => '6989', '1' => '7463', '2' => '7937', '3' => '8410'],
                'B' => ['basic' => '5397', '1' => '5910', '2' => '6433', '3' => '7002', '4' => '7580'],
                'C' => ['basic' => '6030', '1' => '6550', '2' => '6746', '3' => '6989', '4' => '7228', '5' => '7463', '6' => '7757', '7' => '7937', '8' => '8175', '9' => '8410'],
                'D' => ['basic' => '7188', '1' => '7474', '2' => '7772', '3' => '8086', '4' => '8407', '5' => '8746', '6' => '9094', '7' => '9459', '8' => '9838', '9' => '10228', '10' => '10641'],
                'E' => ['basic' => '7466', '1' => '7766', '2' => '8077', '3' => '8397', '4' => '8739', '5' => '9087', '6' => '9450', '7' => '9831', '8' => '10221', '9' => '10634', '10' => '11057'],
            ],
        ],
        'captain' => [
            'name' => 'Captain / Lieutenant (Navy)',
            'short' => 'Captain',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '8861', '1' => '9196', '2' => '9534', '3' => '9870', '4' => '10197', '5' => '10513', '6' => '10825', '7' => '11147', '8' => '11334', '9' => '11521', '10' => '11712'],
            ],
        ],
        'major' => [
            'name' => 'Major / Lieutenant-Commander',
            'short' => 'Major',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '11983', '1' => '12192', '2' => '12401', '3' => '12608', '4' => '12815', '5' => '13019', '6' => '13228', '7' => '13435'],
            ],
        ],
        'lieutenant-colonel' => [
            'name' => 'Lieutenant-Colonel / Commander',
            'short' => 'Lieutenant-Colonel',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '13887', '1' => '14111', '2' => '14329', '3' => '14554', '4' => '14779'],
            ],
        ],
        'colonel' => [
            'name' => 'Colonel / Captain (Navy)',
            'short' => 'Colonel',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '15684', '1' => '16303', '2' => '16923', '3' => '17541'],
            ],
        ],
        'brigadier-general' => [
            'name' => 'Brigadier-General / Commodore',
            'short' => 'Brigadier-General',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '18565', '1' => '19060', '2' => '19585', '3' => '20097'],
            ],
        ],
        'major-general' => [
            'name' => 'Major-General / Rear-Admiral',
            'short' => 'Major-General',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '21301', '1' => '22523', '2' => '23790', '3' => '25020'],
            ],
        ],
        'lieutenant-general' => [
            'name' => 'Lieutenant-General / Vice-Admiral',
            'short' => 'Lieutenant-General',
            'group' => 'officer',
            'levels' => [
                'standard' => ['basic' => '27361', '1' => '28102', '2' => '28879', '3' => '29619'],
            ],
        ],
    ],
];
