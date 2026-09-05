<?php

/**
 * Official sources for the 2026 tax-year rule set.
 *
 * Do not invent citations. Update this file when a new CRA/RQ edition is applied.
 */
return [
    'tax_year' => 2026,
    'edition' => 'CRA T4127 123rd edition (July 1, 2026) with January 1, 2026 baseline',
    'effective_date' => '2026-07-01',
    'retrieved_date' => '2026-09-05',
    'notes' => [
        'Federal brackets, CPP, EI, and most provincial/territorial tables are taken from CRA T4127 Payroll Deductions Formulas.',
        'July 1, 2026 mid-year changes are applied for British Columbia (lowest rate and tax reduction), Newfoundland and Labrador (basic personal amount), and Prince Edward Island (new top bracket). CRA states there was no July change for AB, MB, NB, NT, NS, NU, ON, SK, YT.',
        'Quebec provincial tax is not calculated by CRA T4127 (factor T2 = 0). Quebec income tax, QPP, and QPIP use Revenu Québec / Retraite Québec / Ministère des Finances published 2026 parameters.',
        'This engine estimates a full-year employee with constant pay, claim code 1 (basic personal amount only), and no taxable benefits. It is not a substitute for PDOC or an employer payroll system.',
        'Federal basic personal amount uses the CRA BPAF formula. Manitoba uses BPAMB. Yukon uses BPAYT = BPAF. Nova Scotia uses the fixed 2026 maximum (no income phase-out).',
        'Additional CPP/QPP contributions (first additional + CPP2/QPP2) reduce taxable income (factor F5), matching T4127 Option 1.',
    ],
    'sources' => [
        [
            'title' => 'Payroll Deductions Formulas – 123rd Edition Effective July 1, 2026 (T4127)',
            'url' => 'https://www.canada.ca/en/revenue-agency/services/forms-publications/payroll/t4127-payroll-deductions-formulas/t4127-jul/t4127-jul-payroll-deductions-formulas.html',
            'publisher' => 'Canada Revenue Agency',
            'used_for' => 'July 2026 federal and provincial rates, constants, BC/NL/PE mid-year changes',
        ],
        [
            'title' => 'Payroll Deductions Formulas – 122nd Edition Effective January 1, 2026 (T4127)',
            'url' => 'https://www.canada.ca/en/revenue-agency/services/forms-publications/payroll/t4127-payroll-deductions-formulas/t4127-jan/t4127-jan-payroll-deductions-formulas-computer-programs.html',
            'publisher' => 'Canada Revenue Agency',
            'used_for' => 'Option 1 formulas (A, T3, T1, T4, T2, T), BPAF/BPAMB/BPAYT, CPP/QPP/EI/QPIP formulas, Ontario health premium and tax reduction, Alberta K5P, Yukon K4P',
        ],
        [
            'title' => 'T4032-OC Payroll Deductions Tables – CPP, EI, and income tax deductions – In Canada beyond the limits of any province/territory or outside Canada (January 1, 2026)',
            'url' => 'https://www.canada.ca/content/dam/cra-arc/migration/cra-arc/tx/bsnss/tpcs/pyrll/t4032/2026/t4032-oc-1-26e.pdf',
            'publisher' => 'Canada Revenue Agency',
            'used_for' => '2026 federal brackets, Canada Employment Amount, federal BPA min/max, CPP YMPE/YAMPE, EI MIE, worked examples',
        ],
        [
            'title' => 'CPP contribution rates, maximums and exemptions',
            'url' => 'https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/payroll-deductions-contributions/canada-pension-plan-cpp/cpp-contribution-rates-maximums-exemptions.html',
            'publisher' => 'Canada Revenue Agency',
            'used_for' => '2026 YMPE $74,600, basic exemption $3,500, employee rate 5.95%, maximum $4,230.45',
        ],
        [
            'title' => 'T4127 – Current year (landing page)',
            'url' => 'https://www.canada.ca/en/revenue-agency/services/tax/businesses/topics/payroll/t4127-payroll-deductions-formulas-computer-programs.html',
            'publisher' => 'Canada Revenue Agency',
            'used_for' => 'Confirms the July 1, 2026 formulas guide is the current T4127 edition',
        ],
        [
            'title' => 'TD1 2026 Personal Tax Credits Return',
            'url' => 'https://www.canada.ca/content/dam/cra-arc/formspubs/pbg/td1/td1-26e.pdf',
            'publisher' => 'Canada Revenue Agency',
            'used_for' => 'Federal basic personal amount $16,452 and income-based partial claim guidance',
        ],
        [
            'title' => 'Income Tax Rates (2026 taxation year)',
            'url' => 'https://www.revenuquebec.ca/en/citizens/income-tax-return/completing-your-income-tax-return/income-tax-rates/',
            'publisher' => 'Revenu Québec',
            'used_for' => 'Quebec 2026 personal income tax brackets',
        ],
        [
            'title' => 'Parameters of the Personal Income Tax System for 2026',
            'url' => 'https://cdn-contenu.quebec.ca/cdn-contenu/adm/min/finances/publications-adm/parametres/AUTEN_IncomeTax2026.pdf',
            'publisher' => 'Ministère des Finances du Québec',
            'used_for' => 'Quebec 2026 basic personal amount $18,952 and workers deduction $1,450',
        ],
    ],
];
