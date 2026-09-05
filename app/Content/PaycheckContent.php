<?php

namespace App\Content;

use App\Support\Province;

class PaycheckContent
{
    /**
     * @return list<array{question: string, answer: string}>
     */
    public function nationalFaqs(): array
    {
        $year = config('tax.current_year');

        return [
            [
                'question' => 'How is take-home pay calculated in Canada?',
                'answer' => "Estimated take-home pay is your gross salary minus federal and provincial or territorial income tax, CPP or QPP, EI, and any optional deductions you enter. This calculator uses CRA T4127 payroll deduction formulas for {$year}, with Revenu Québec parameters for Quebec provincial tax.",
            ],
            [
                'question' => 'How much tax do I pay on my salary?',
                'answer' => 'Canada uses progressive tax brackets federally and in each province or territory. You do not pay the top rate on your whole salary. This page shows estimated income tax and an effective tax rate, which is total estimated tax divided by gross salary.',
            ],
            [
                'question' => 'How much CPP is deducted?',
                'answer' => 'For 2026, employees contribute 5.95% of pensionable earnings between the $3,500 basic exemption and the $74,600 YMPE, up to $4,230.45. Earnings between $74,600 and $85,000 also attract a 4% second additional contribution (CPP2), up to $416. Quebec employees contribute to QPP instead.',
            ],
            [
                'question' => 'How much EI is deducted?',
                'answer' => 'For 2026, the employee EI rate outside Quebec is 1.63% of insurable earnings up to $68,900, with a maximum premium of $1,123.07. Quebec employees pay a lower EI rate and also pay QPIP.',
            ],
            [
                'question' => 'What is the difference between gross and net pay?',
                'answer' => 'Gross pay is your salary before deductions. Net pay — take-home — is what remains after income tax, CPP or QPP, EI, and other payroll deductions such as RRSP or union dues.',
            ],
            [
                'question' => 'Why does my actual paycheck differ from this calculator?',
                'answer' => 'Employers may withhold differently based on your TD1 claims, taxable benefits, pension plan, union dues, additional tax requested, multiple jobs, or mid-year hiring. This tool estimates a full-year employee claiming the basic personal amount.',
            ],
            [
                'question' => 'Is this calculator accurate?',
                'answer' => 'It follows official CRA payroll formulas and published 2026 rates. It is still an estimate. Use the CRA Payroll Deductions Online Calculator or a qualified professional for official withholding.',
            ],
            [
                'question' => 'How often are Canadian tax brackets updated?',
                'answer' => 'Federal brackets and many provincial amounts are indexed each January. Some provinces also make mid-year changes. This site versions tax data by year so annual updates stay isolated from the rest of the application.',
            ],
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function provinceFaqs(Province $province): array
    {
        $specific = $this->province($province)['faqs'];

        return array_merge($specific, $this->nationalFaqs());
    }

    /**
     * @return array{intro: string, body: list<array{heading: string, copy: string}>, faqs: list<array{question: string, answer: string}>}
     */
    public function province(Province $province): array
    {
        return match ($province) {
            Province::Ontario => [
                'intro' => 'Estimate your Ontario take-home pay after federal tax, Ontario tax, the Ontario Health Premium, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Ontario income tax',
                        'copy' => 'Ontario has five personal tax brackets for 2026, starting at 5.05% and rising to 13.16% above $220,000. Ontario also applies a surtax on provincial tax over $5,818, plus a separate Ontario Health Premium that can reach $900 a year. A low-income tax reduction of $300 can wipe out Ontario tax for some lower earners.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Ontario employees pay the same federal brackets, Canada Pension Plan, and Employment Insurance as other provinces outside Quebec. The 2026 federal basic personal amount is $16,452 for most filers. Ontario’s own basic personal amount is $12,989.',
                    ],
                    [
                        'heading' => 'Ontario-specific considerations',
                        'copy' => 'The Ontario Health Premium is based on taxable income and is not reduced by the Ontario tax reduction. Taxable benefits, employer pensions, and your TD1ON claim can change withholding even when salary stays the same.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Does this Ontario calculator include the Health Premium?',
                        'answer' => 'Yes. The Ontario Health Premium is included in estimated provincial tax using the CRA T4127 V2 formula.',
                    ],
                    [
                        'question' => 'What is Ontario’s basic personal amount for 2026?',
                        'answer' => 'The 2026 Ontario basic personal amount used here is $12,989, from CRA T4127 Table 8.2.',
                    ],
                ],
            ],
            Province::Alberta => [
                'intro' => 'Estimate Alberta take-home pay after federal tax, Alberta’s comparatively low provincial brackets, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Alberta income tax',
                        'copy' => 'Alberta’s 2026 lowest rate is 8%, with additional brackets at 10%, 12%, 13%, 14%, and 15%. The provincial basic personal amount is $22,769 — among the highest in Canada — which keeps more of a mid-range salary in your pocket before other deductions.',
                    ],
                    [
                        'heading' => 'Alberta tax credit',
                        'copy' => 'CRA payroll formulas include an Alberta tax credit (factor K5P) that can reduce provincial tax when personal and CPP/EI credits exceed $4,896. This calculator applies that credit automatically.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Alberta employees still pay federal income tax, CPP, and EI at national rates. There is no provincial health premium like Ontario’s, so the provincial line is usually smaller than in higher-tax provinces.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Why is Alberta take-home often higher than Ontario’s?',
                        'answer' => 'Alberta combines a higher basic personal amount with no health premium and a different bracket structure. Your federal tax, CPP, and EI are still calculated the same way.',
                    ],
                ],
            ],
            Province::BritishColumbia => [
                'intro' => 'Estimate British Columbia take-home pay using the July 1, 2026 payroll rates, including the BC tax reduction.',
                'body' => [
                    [
                        'heading' => 'British Columbia income tax',
                        'copy' => 'For payroll starting July 1, 2026, CRA uses a prorated lowest BC rate of 6.14% on income under $50,363, then rates up to 20.50%. The BC basic personal amount is $13,216.',
                    ],
                    [
                        'heading' => 'BC tax reduction',
                        'copy' => 'Lower-income employees may receive a BC tax reduction (factor S). For July 2026 payroll, the basic reduction used in Option 1 is $805, fully available at incomes up to $25,570 and phased out by $44,952.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'BC employees pay federal tax, CPP, and EI. Mid-year BC rate changes affect provincial withholding only; CPP and EI limits remain the national 2026 amounts.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Which BC tax rate does this calculator use?',
                        'answer' => 'The July 1, 2026 T4127 Option 1 rates, including the prorated 6.14% lowest rate used for the second half of 2026.',
                    ],
                ],
            ],
            Province::Quebec => [
                'intro' => 'Estimate Quebec take-home pay after federal tax (with the Quebec abatement), Quebec provincial tax, QPP, EI, and QPIP.',
                'body' => [
                    [
                        'heading' => 'Quebec income tax',
                        'copy' => 'Quebec administers its own provincial tax. For 2026 the brackets are 14%, 19%, 24%, and 25.75%, and the basic personal amount is $18,952. CRA T4127 does not calculate Quebec provincial tax; this estimate uses Revenu Québec and Ministère des Finances published parameters.',
                    ],
                    [
                        'heading' => 'QPP and QPIP instead of CPP only',
                        'copy' => 'Quebec employees contribute to the Quebec Pension Plan at 6.30% up to the $74,600 MPE (maximum $4,479.30), plus QPP2 above that ceiling. They also pay Quebec Parental Insurance Plan premiums and a lower federal EI rate.',
                    ],
                    [
                        'heading' => 'Federal abatement',
                        'copy' => 'Federal tax for Quebec residents is reduced by a 16.5% abatement, which this calculator applies. Your pay stub will still show federal tax, QPP, EI, QPIP, and Quebec provincial tax as separate lines.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Do Quebec employees pay CPP?',
                        'answer' => 'No. Quebec employees pay QPP (and QPP2 where applicable) instead of CPP. They also pay QPIP and a reduced EI premium.',
                    ],
                    [
                        'question' => 'Is Quebec provincial tax from CRA tables?',
                        'answer' => 'No. CRA sets T2 to zero for Quebec. The provincial estimate here uses official 2026 Revenu Québec rates and the $18,952 basic personal amount.',
                    ],
                ],
            ],
            Province::NewBrunswick => [
                'intro' => 'Estimate New Brunswick take-home pay after federal tax, New Brunswick’s four provincial brackets, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'New Brunswick income tax',
                        'copy' => 'New Brunswick’s 2026 brackets are 9.40%, 14.00%, 16.00%, and 19.50%, with the first threshold at $52,333. The provincial basic personal amount is $13,664. CRA recorded no July 2026 change for New Brunswick.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'A New Brunswick paycheck still withholds federal tax, CPP, and EI. Optional RRSP or pension contributions entered here reduce estimated taxable income the same way CRA factor F does.',
                    ],
                    [
                        'heading' => 'Using this page for job offers',
                        'copy' => 'Compare offers by salary and pay frequency. Biweekly and monthly net amounts are the same annual estimate split across 26 or 12 periods.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'What is New Brunswick’s 2026 basic personal amount?',
                        'answer' => 'CRA T4127 Table 8.2 lists $13,664 for New Brunswick in 2026.',
                    ],
                ],
            ],
            Province::NovaScotia => [
                'intro' => 'Estimate Nova Scotia take-home pay after federal tax, Nova Scotia’s five brackets, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Nova Scotia income tax',
                        'copy' => 'Nova Scotia’s 2026 rates start at 8.79% and reach 21% above $157,124. For 2026, CRA removed the income-tested BPANS formula; the basic personal amount is the fixed maximum of $11,932 for all employees who do not file a different TD1NS claim.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Nova Scotia employees pay federal tax, CPP, and EI at the national 2026 rates. The relatively lower provincial basic amount means more provincial tax can appear on mid-range salaries than in provinces with larger personal amounts.',
                    ],
                    [
                        'heading' => 'Nova Scotia-specific considerations',
                        'copy' => 'Taxable benefits and additional TD1 claims still change withholding. This estimate assumes claim code 1 only.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Is the Nova Scotia basic personal amount still income-tested?',
                        'answer' => 'Not for 2026 payroll. CRA instructs that the BPANS formula be removed and the maximum amount used.',
                    ],
                ],
            ],
            Province::Manitoba => [
                'intro' => 'Estimate Manitoba take-home pay after federal tax, Manitoba’s three brackets, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Manitoba income tax',
                        'copy' => 'Manitoba uses three 2026 brackets: 10.80% under $47,000, 12.75% to $100,000, and 17.40% above that. The province is not indexing those thresholds or the basic personal amount for 2026.',
                    ],
                    [
                        'heading' => 'Manitoba basic personal amount',
                        'copy' => 'The maximum BPAMB is $15,780. CRA phases it out between $200,000 and $400,000 of net income, which this calculator applies for high earners.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Manitoba employees pay the same federal tax, CPP, and EI as other provinces outside Quebec.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Does Manitoba still index tax brackets?',
                        'answer' => 'No. For 2025 and subsequent years, Manitoba announced that BPAMB and the provincial brackets are not indexed. The 2026 amounts remain $47,000 / $100,000 and a $15,780 maximum personal amount.',
                    ],
                ],
            ],
            Province::Saskatchewan => [
                'intro' => 'Estimate Saskatchewan take-home pay after federal tax, Saskatchewan’s three brackets, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Saskatchewan income tax',
                        'copy' => 'Saskatchewan’s 2026 rates are 10.50%, 12.50%, and 14.50%, with thresholds at $54,532 and $155,805. The basic personal amount is $20,381, reflecting both indexation and The Saskatchewan Affordability Act increases.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Saskatchewan paycheques also withhold federal tax, CPP, and EI. The larger provincial personal amount is one reason take-home can look stronger than in provinces with smaller credits.',
                    ],
                    [
                        'heading' => 'Saskatchewan-specific considerations',
                        'copy' => 'This estimate does not apply labour-sponsored fund credits or dependent amounts. Enter pension or RRSP deductions if your employer withholds them.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Why is Saskatchewan’s basic personal amount high in 2026?',
                        'answer' => 'The province is raising several personal amounts by $500 a year for four years, on top of indexation. CRA lists $20,381 for 2026.',
                    ],
                ],
            ],
            Province::NewfoundlandAndLabrador => [
                'intro' => 'Estimate Newfoundland and Labrador take-home pay using the July 1, 2026 prorated basic personal amount.',
                'body' => [
                    [
                        'heading' => 'Newfoundland and Labrador income tax',
                        'copy' => 'The province has eight 2026 brackets, from 8.70% to 21.80% on income above $1,141,275. That wider top-end structure is unique in Canada.',
                    ],
                    [
                        'heading' => 'July 2026 basic personal amount',
                        'copy' => 'Newfoundland and Labrador increased the BPA to $13,094 effective January 1, 2026, then CRA applied a prorated $15,000 amount for July–December payroll so employees catch up. This calculator uses the July 1 Option 1 amount of $15,000.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Federal tax, CPP, and EI still apply at national rates. Mid-year BPA changes affect provincial withholding only.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Which NL basic personal amount is used?',
                        'answer' => 'The July 1, 2026 CRA Option 1 amount of $15,000, which employers use for the second half of 2026.',
                    ],
                ],
            ],
            Province::PrinceEdwardIsland => [
                'intro' => 'Estimate Prince Edward Island take-home pay, including the new 2026 top bracket on income over $200,000.',
                'body' => [
                    [
                        'heading' => 'Prince Edward Island income tax',
                        'copy' => 'PEI’s 2026 brackets run from 9.50% to a new top rate. For July 1 payroll, CRA uses a prorated 21% rate on taxable income above $200,000. The basic personal amount is $15,000 and is not indexed.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'PEI employees pay federal tax, CPP, and EI. Most salaries under $200,000 are unaffected by the new top provincial bracket.',
                    ],
                    [
                        'heading' => 'PEI-specific considerations',
                        'copy' => 'Because PEI amounts are not indexed, the $15,000 personal amount stays put unless the province legislates a change.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'What changed in PEI tax for July 2026?',
                        'answer' => 'A new bracket on income above $200,000. CRA’s July Option 1 rate for that bracket is the prorated 21%.',
                    ],
                ],
            ],
            Province::NorthwestTerritories => [
                'intro' => 'Estimate Northwest Territories take-home pay after federal tax, territorial brackets, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Northwest Territories income tax',
                        'copy' => 'The NWT 2026 rates are 5.90%, 8.60%, 12.20%, and 14.05%, with a basic personal amount of $18,198. There was no July 2026 CRA change for the territory.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Territorial employees pay the same federal tax, CPP, and EI as provincial employees outside Quebec. Northern residence deductions (prescribed zone) are not applied here unless you model them separately.',
                    ],
                    [
                        'heading' => 'Northern considerations',
                        'copy' => 'If you claim the northern residents deduction on a TD1, your actual withholding can be lower than this estimate.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Does this include the northern residents deduction?',
                        'answer' => 'No. Factor HD is not modeled in this first release. The estimate uses the standard territorial basic personal amount only.',
                    ],
                ],
            ],
            Province::Nunavut => [
                'intro' => 'Estimate Nunavut take-home pay after federal tax, Nunavut’s lower territorial rates, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Nunavut income tax',
                        'copy' => 'Nunavut’s 2026 rates start at 4.00% and rise to 11.50% above $181,439. The basic personal amount is $19,659. Combined with federal tax, that is still a full Canadian tax picture — just with a smaller territorial slice.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Nunavut employees pay federal tax, CPP, and EI at the same 2026 rates used elsewhere outside Quebec.',
                    ],
                    [
                        'heading' => 'Northern considerations',
                        'copy' => 'Prescribed-zone deductions can reduce taxable income on a real return. They are not entered on this simple calculator.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Are Nunavut tax rates lower than most provinces?',
                        'answer' => 'The 4% starting territorial rate is the lowest in Canada. Federal tax, CPP, and EI still apply.',
                    ],
                ],
            ],
            Province::Yukon => [
                'intro' => 'Estimate Yukon take-home pay after federal tax, Yukon brackets that track federal thresholds, CPP, and EI.',
                'body' => [
                    [
                        'heading' => 'Yukon income tax',
                        'copy' => 'Yukon’s 2026 brackets largely follow federal thresholds, with rates from 6.40% to 15% above $500,000. Yukon’s basic personal amount uses the same BPAF formula as the federal amount, including the high-income phase-out.',
                    ],
                    [
                        'heading' => 'Yukon employment amount',
                        'copy' => 'CRA applies a territorial Canada employment amount credit (K4P) at Yukon’s lowest rate. This calculator includes that credit.',
                    ],
                    [
                        'heading' => 'Federal tax, CPP, and EI',
                        'copy' => 'Yukon employees pay federal tax, CPP, and EI. Northern residents deductions are not applied in this estimate.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Does Yukon use the federal basic personal amount?',
                        'answer' => 'Yes. CRA sets BPAYT equal to BPAF, so Yukon’s personal amount phases down between $181,440 and $258,482 of net income.',
                    ],
                ],
            ],
        };
    }
}
