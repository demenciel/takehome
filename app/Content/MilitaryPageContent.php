<?php

namespace App\Content;

class MilitaryPageContent
{
    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        $year = config('tax.current_year');

        return [
            'h1' => 'Canadian Armed Forces Salary Calculator',
            'title' => 'Canadian Armed Forces Salary Calculator '.$year.' | Paycheque.app',
            'description' => 'Estimate CAF take-home pay by component, rank, pay increment, and province. Uses official Regular Force and Reserve Force pay tables and the Paycheque.app payroll engine.',
            'intro' => 'Estimate CAF take-home pay by component, rank, pay increment, and province. Base pay comes from official National Defence tables. Income tax, CPP or QPP, and EI are estimated with the same engine as the paycheck calculator.',
            'sections' => [
                [
                    'heading' => 'How CAF pay works',
                    'copy' => 'Regular Force members and reservists on Class C service are paid a monthly rate for their rank, pay level, and pay increment. Reserve Force Class A and Class B service is paid a published daily rate. This calculator uses those published base-pay tables. It does not invent occupation-specific or specialist rates.',
                ],
                [
                    'heading' => 'Regular Force vs Reserve Force pay',
                    'copy' => 'Choose Regular Force for the official monthly scale (also used for Reserve Class C). Choose Reserve Force for Class A or Class B daily rates, then enter how many days you expect to be paid in a year. Daily rates are stored separately — they are not monthly pay divided by 30.',
                ],
                [
                    'heading' => 'Pay increments',
                    'copy' => 'A pay increment is the step on the published scale for that rank. “Basic” is the starting rate on the official table. Higher numbered increments are later annual steps. Some officer and CWO ranks also have pay levels (A, B, C, and so on) for different entry plans or appointments.',
                ],
                [
                    'heading' => 'Taxes and payroll deductions',
                    'copy' => 'Federal and provincial or territorial income tax, CPP or QPP (including CPP2/QPP2), EI, and QPIP in Quebec use the same '.$year.' payroll formulas as the rest of this site. Regular Force pension is estimated from official Treasury Board rates for 2026. Reserve Force pension is not estimated.',
                ],
                [
                    'heading' => 'What is not included',
                    'copy' => 'Allowances and benefits are not included unless entered manually. That means PLD/CFHD, LDA, sea duty, aircrew, special operations, deployment, environmental allowances, Military Service Pay, and other CAF benefits are omitted. Specialist, Special Forces, Search and Rescue, pilot, legal, medical, and dental occupation tables are also omitted.',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Are these the current official CAF pay rates?',
                    'answer' => 'Yes for base pay. National Defence still publishes Regular Force monthly and Reserve Force daily tables as effective 1 April 2025. Those remain the official published rank scales as of this page’s retrieved date. April 2026 CAF compensation updates were mainly allowances, which this calculator does not estimate.',
                ],
                [
                    'question' => 'Does this include CAF pension?',
                    'answer' => 'For Regular Force only. The estimate uses the Treasury Board 2026 rates of 9.10% up to the YMPE and 11.69% above it. It does not model members who have reached 35 years of pensionable service. Reserve Force pension is not included.',
                ],
                [
                    'question' => 'Why is my actual CAF pay different?',
                    'answer' => 'Occupation group, specialist pay, allowances, posting, Class of reserve service, and your real pensionable service all change a pay statement. This page estimates standard-occupation base pay plus optional amounts you type in.',
                ],
            ],
        ];
    }
}
