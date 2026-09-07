<?php

return [
    'etsy' => [
        'parental_leave_planner' => env('ETSY_PARENTAL_LEAVE_PLANNER_URL'),
        'new_baby_planner' => env('ETSY_NEW_BABY_PLANNER_URL'),
    ],
    'government' => [
        'ei_maternity_parental' => 'https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental.html',
        'ei_benefit_amount' => 'https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental/benefit-amount.html',
        'ei_eligibility' => 'https://www.canada.ca/en/services/benefits/ei/ei-maternity-parental/eligibility.html',
        'qpip' => 'https://www.rqap.gouv.qc.ca/en',
        'ccb_calculator' => 'https://www.canada.ca/en/revenue-agency/services/child-family-benefits/child-family-benefits-calculator.html',
        'ccb_overview' => 'https://www.canada.ca/en/revenue-agency/services/child-family-benefits/canada-child-benefit-overview.html',
    ],
];
