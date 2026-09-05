<?php

namespace App\Content;

final class CalculatorHubContent
{
    /**
     * Unique copy for specialized calculator hubs. Each page must answer a
     * different search intent; do not clone the national page.
     *
     * @return array{
     *     key: string,
     *     route: string,
     *     title: string,
     *     description: string,
     *     h1: string,
     *     intro: string,
     *     calculator: string,
     *     frequency: string|null,
     *     input_mode: string|null,
     *     sections: list<array{heading: string, copy: string}>,
     *     faqs: list<array{question: string, answer: string}>
     * }
     */
    public function hub(string $key): array
    {
        return match ($key) {
            'paycheque' => [
                'key' => 'paycheque',
                'route' => 'tools.paycheque',
                'title' => 'Canadian Paycheque Calculator — Take-home by province',
                'description' => 'Estimate your Canadian paycheque after federal tax, provincial tax, CPP or QPP, and EI. Enter salary, province, and how often you are paid.',
                'h1' => 'Canadian Paycheque Calculator',
                'intro' => 'A paycheque is the amount that remains after payroll deductions. This page uses the Canadian spelling and the same 2026 CRA payroll formulas as the rest of the site.',
                'calculator' => 'paycheck',
                'frequency' => null,
                'input_mode' => null,
                'sections' => [
                    [
                        'heading' => 'What a Canadian paycheque usually deducts',
                        'copy' => 'A typical employee paycheque withholds federal income tax, provincial or territorial income tax, CPP or QPP, and EI. Quebec employees also see QPIP and a lower EI rate. Optional items such as RRSP, pension, or union dues only appear if your employer deducts them.',
                    ],
                    [
                        'heading' => 'Paycheque vs paycheck',
                        'copy' => 'The calculation is the same. Canadians usually search “paycheque”; US-style “paycheck” is also common. Use this page if you want the Canadian spelling; the Canada paycheck calculator is the same engine with a national framing.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Is a paycheque the same as take-home pay?',
                        'answer' => 'Yes in everyday use. This estimate is net pay after income tax and statutory payroll contributions, not your gross salary.',
                    ],
                ],
            ],
            'take_home' => [
                'key' => 'take_home',
                'route' => 'tools.take_home',
                'title' => 'Take-Home Pay Calculator for Canadians',
                'description' => 'Calculate estimated take-home pay in Canada after income tax, CPP or QPP, and EI. Compare annual, monthly, biweekly, and weekly net pay.',
                'h1' => 'Take-Home Pay Calculator',
                'intro' => 'Take-home pay is what remains after payroll deductions. Use this calculator when you care about net income, not the tax-bracket headline rate.',
                'calculator' => 'paycheck',
                'frequency' => null,
                'input_mode' => null,
                'sections' => [
                    [
                        'heading' => 'Gross salary is not take-home',
                        'copy' => 'An $80,000 offer is not $80,000 in your bank account. Federal tax, provincial tax, and CPP/EI come off first. The effective tax rate on this site is estimated income tax divided by gross salary — it does not include CPP and EI, which are contributions rather than income tax.',
                    ],
                    [
                        'heading' => 'Why take-home changes by province',
                        'copy' => 'Federal tax, CPP, and EI (outside Quebec) are national. Provincial brackets, basic personal amounts, and extras such as Ontario’s Health Premium change the provincial line. Quebec replaces CPP with QPP and adds QPIP.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Does take-home include CPP and EI?',
                        'answer' => 'Yes. Those contributions reduce the deposit even though they are not income tax. The effective tax rate shown here is income tax only.',
                    ],
                ],
            ],
            'salary_after_tax' => [
                'key' => 'salary_after_tax',
                'route' => 'tools.salary_after_tax',
                'title' => 'Salary After Tax Calculator — Canada',
                'description' => 'See how much of a Canadian salary remains after federal tax, provincial tax, CPP or QPP, and EI for the current tax year.',
                'h1' => 'Salary After Tax Calculator',
                'intro' => 'This page answers “how much is this salary after tax?” Choose a province — Canada does not have a single after-tax number that works everywhere.',
                'calculator' => 'paycheck',
                'frequency' => 'annual',
                'input_mode' => null,
                'sections' => [
                    [
                        'heading' => 'After-tax salary is not one number',
                        'copy' => 'The same $70,000 salary produces different net income in Alberta, Ontario, and Quebec because provincial tax and pension plans differ. Always pick a jurisdiction before comparing offers.',
                    ],
                    [
                        'heading' => 'What “after tax” includes here',
                        'copy' => 'The estimate subtracts federal income tax, provincial or territorial income tax, CPP or QPP, EI, and QPIP in Quebec. It does not subtract rent, benefits, or RRSP unless you enter those deductions.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Is after-tax salary the same as net pay?',
                        'answer' => 'On a pay stub, net pay can also include benefits, extra tax requested, or pension. This tool estimates statutory deductions plus any optional amounts you type in.',
                    ],
                ],
            ],
            'biweekly' => [
                'key' => 'biweekly',
                'route' => 'tools.biweekly',
                'title' => 'Biweekly Pay Calculator — Canada',
                'description' => 'Estimate Canadian biweekly take-home pay. The annual tax, CPP, and EI totals are split across 26 pay periods.',
                'h1' => 'Biweekly Pay Calculator',
                'intro' => 'Biweekly pay is 26 cheques a year. This calculator estimates the annual deductions first, then divides net pay by 26 so the biweekly amount stays consistent with the yearly total.',
                'calculator' => 'paycheck',
                'frequency' => 'biweekly',
                'input_mode' => null,
                'sections' => [
                    [
                        'heading' => 'Why 26, not 24',
                        'copy' => 'Biweekly is every two weeks (26 periods). Semimonthly is twice a month (24). A $2,000 biweekly cheque is not the same annual pay as $2,000 semimonthly.',
                    ],
                    [
                        'heading' => 'How this site splits a year',
                        'copy' => 'Income tax, CPP, and EI are estimated on an annual basis using CRA Option 1, then divided by 26. Real employers may true-up CPP or EI mid-year when a ceiling is reached, so an individual cheque can differ even when the yearly total is close.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'How many biweekly paycheques are there in a year?',
                        'answer' => '26. Some years a weekday calendar produces 27 pay dates; this estimate always uses 26 so annual and biweekly figures stay internally consistent.',
                    ],
                ],
            ],
            'weekly' => [
                'key' => 'weekly',
                'route' => 'tools.weekly',
                'title' => 'Weekly Pay Calculator — Canada',
                'description' => 'Estimate Canadian weekly take-home pay by dividing the annual after-tax estimate across 52 weeks.',
                'h1' => 'Weekly Pay Calculator',
                'intro' => 'Weekly pay is 52 periods. Enter an annual salary or hourly wage; the engine estimates yearly deductions, then shows the weekly net amount.',
                'calculator' => 'paycheck',
                'frequency' => 'weekly',
                'input_mode' => null,
                'sections' => [
                    [
                        'heading' => 'Weekly vs hourly',
                        'copy' => 'Hourly jobs are often paid weekly. You can enter an hourly wage on the form; this site annualizes it using the hours you provide, then estimates tax on that annual figure.',
                    ],
                    [
                        'heading' => 'CPP exemption on weekly pay',
                        'copy' => 'CRA allocates the $3,500 CPP basic exemption across pay periods. This calculator uses the annual method and then divides. A live payroll system can be a few cents different on a given week.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'How is weekly take-home calculated here?',
                        'answer' => 'Annual net pay is estimated with CRA payroll formulas, then divided by 52. It is not a separate weekly tax table.',
                    ],
                ],
            ],
            default => abort(404),
        };
    }

    /**
     * @return array{
     *     key: string,
     *     route: string,
     *     title: string,
     *     description: string,
     *     h1: string,
     *     intro: string,
     *     mode: string,
     *     sections: list<array{heading: string, copy: string}>,
     *     faqs: list<array{question: string, answer: string}>
     * }
     */
    public function conversion(string $key): array
    {
        $hours = (int) config('tax.hours_per_week', 40);
        $weeks = (int) config('tax.weeks_per_year', 52);
        $assumption = "{$hours} hours/week × {$weeks} weeks (".($hours * $weeks).' hours/year)';

        return match ($key) {
            'hourly_to_salary' => [
                'key' => 'hourly_to_salary',
                'route' => 'tools.hourly_to_salary',
                'title' => 'Hourly to Salary Calculator — Canada',
                'description' => 'Convert a Canadian hourly wage to an annual salary using an explicit hours-per-week assumption, then estimate take-home pay if you want the after-tax picture.',
                'h1' => 'Hourly to Salary Calculator',
                'intro' => 'Multiply hourly wage × hours per week × weeks per year. The default assumption on this site is '.$assumption.'. Change the hours if your job is not full-time.',
                'mode' => 'hourly_to_salary',
                'sections' => [
                    [
                        'heading' => 'The conversion is not a tax calculation',
                        'copy' => 'Hourly × hours × weeks is a gross-pay conversion. Tax still depends on province and the annual total. After you convert, use the paycheck calculator to estimate take-home.',
                    ],
                    [
                        'heading' => 'Why we state the hour assumption',
                        'copy' => 'A $40/hour job at 30 hours/week is not a $83,200 salary. Sites that hide the hour count produce misleading “equivalent salaries.”',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'How many hours is a full-time year on this site?',
                        'answer' => $assumption.'. Overtime, unpaid time off, and statutory holidays are not modeled here.',
                    ],
                ],
            ],
            'salary_to_hourly' => [
                'key' => 'salary_to_hourly',
                'route' => 'tools.salary_to_hourly',
                'title' => 'Salary to Hourly Calculator — Canada',
                'description' => 'Convert a Canadian annual salary to an approximate hourly wage using an explicit full-time hour assumption.',
                'h1' => 'Salary to Hourly Calculator',
                'intro' => 'Divide annual salary by hours per year. The default assumption is '.$assumption.'. This is a planning figure, not a contractual wage.',
                'mode' => 'salary_to_hourly',
                'sections' => [
                    [
                        'heading' => 'Salaried jobs do not always track hours',
                        'copy' => 'Many salaried roles expect more or less than 40 hours. Use this page to compare an offer with an hourly job, then adjust hours to match reality.',
                    ],
                    [
                        'heading' => 'After-tax hourly is a different question',
                        'copy' => 'This converter returns a gross hourly equivalent. For after-tax hourly, estimate annual take-home first, then divide by the same hour count.',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'What hourly rate is a $80,000 salary?',
                        'answer' => 'Using '.$assumption.', $80,000 ÷ '.($hours * $weeks).' ≈ $38.46/hour before tax.',
                    ],
                ],
            ],
            default => abort(404),
        };
    }
}
