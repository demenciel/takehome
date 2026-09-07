<?php

/**
 * Canadian parental-leave benefit parameters, versioned by year.
 *
 * Do not invent rates. Update this file when Service Canada publishes a new EI year.
 */
return [
    'year' => (int) env('CURRENT_TAX_YEAR', 2026),
    'last_reviewed' => '2026-09-07',
    'edition' => 'EI maternity and parental benefits, 2026',
    'years' => [
        2026 => [
            'ei' => [
                'maximum_insurable_earnings' => '68900.00',
                'maternity' => [
                    'rate' => '0.55',
                    'max_weekly' => '729.00',
                    'max_weeks' => 15,
                    'window_weeks' => 52,
                ],
                'standard_parental' => [
                    'rate' => '0.55',
                    'max_weekly' => '729.00',
                    'shared_max_weeks' => 40,
                    'individual_max_weeks' => 35,
                    'window_weeks' => 52,
                ],
                'extended_parental' => [
                    'rate' => '0.33',
                    'max_weekly' => '437.00',
                    'shared_max_weeks' => 69,
                    'individual_max_weeks' => 61,
                    'window_weeks' => 78,
                ],
            ],
        ],
    ],
    'sources' => [
        [
            'title' => 'EI maternity and parental benefits',
            'url' => 'https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental.html',
            'publisher' => 'Government of Canada',
            'used_for' => 'Program overview, eligibility, and claiming rules',
        ],
        [
            'title' => 'EI maternity and parental benefits: How much you can receive',
            'url' => 'https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental/benefit-amount.html',
            'publisher' => 'Government of Canada',
            'used_for' => '2026 benefit rates, weekly maxima, and standard vs extended weeks',
        ],
        [
            'title' => 'EI maternity and parental benefits: Eligibility',
            'url' => 'https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental/eligibility.html',
            'publisher' => 'Government of Canada',
            'used_for' => 'Insurable-hours and eligibility requirements',
        ],
        [
            'title' => 'Québec Parental Insurance Plan',
            'url' => 'https://www.rqap.gouv.qc.ca/en',
            'publisher' => 'Gouvernement du Québec',
            'used_for' => 'Québec maternity, paternity, parental, and adoption benefits (QPIP)',
        ],
    ],
    'disclaimer' => 'Estimates only. Actual EI benefits depend on your insurable earnings, eligibility, Service Canada calculations, and individual circumstances. This is not a Service Canada or Government of Canada service.',
];
