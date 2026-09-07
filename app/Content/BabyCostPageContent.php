<?php

namespace App\Content;

class BabyCostPageContent
{
    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        $year = (int) config('tax.current_year');
        $ccb = config('baby-budget.ccb');

        return [
            'h1' => 'Canadian Baby Cost Calculator',
            'title' => 'Baby Cost Calculator Canada '.$year.' | Paycheque.app',
            'description' => 'Plan Canadian baby startup costs, monthly expenses, childcare, and savings with editable defaults. See remaining purchases and the estimated amount left to prepare.',
            'intro' => 'Baby costs vary widely. This calculator does not publish a single “a baby costs $X in Canada” figure. It starts from editable planning defaults so you can mark gifts, used items, skipped purchases, childcare, and savings.',
            'sections' => [
                [
                    'heading' => 'Startup costs vs monthly costs',
                    'copy' => 'Startup costs are one-time preparation purchases: sleep, feeding, diapering, clothing, transportation, health supplies, and nursery items. Monthly costs are recurring first-year expenses such as diapers, formula, and replacements. Edit or skip any line.',
                ],
                [
                    'heading' => 'Childcare',
                    'copy' => 'There is no Canada-wide $10/day assumption here. Enter the monthly amount you actually expect and the month you expect care to start. If you know a subsidy or benefit, subtract it as a monthly amount.',
                ],
                [
                    'heading' => 'Canada Child Benefit',
                    'copy' => 'This page does not estimate CCB. For '.$ccb['period_label'].', the published maximum for a child under 6 is $'.number_format((float) $ccb['under_6_annual_maximum']).' a year, or $'.$ccb['under_6_monthly_maximum'].' a month. Actual payment depends on adjusted family net income and family circumstances. Use the CRA Child and Family Benefits Calculator if you are unsure.',
                ],
                [
                    'heading' => 'Savings and readiness',
                    'copy' => 'Enter savings already set aside, the amount you are saving each month, and months until arrival. If you already estimated a parental-leave income reduction, you can include that optional figure. The headline result is the estimated amount left to prepare — not a warning score.',
                ],
            ],
            'faqs' => $this->faqs($ccb),
        ];
    }

    /**
     * @param  array<string, mixed>  $ccb
     * @return list<array{question: string, answer: string}>
     */
    public function faqs(array $ccb): array
    {
        return [
            [
                'question' => 'How much does a baby cost in Canada?',
                'answer' => 'There is no single national figure. Costs depend on gifts, second-hand items, feeding method, childcare, and where you live. Use the editable categories on this page instead of a fixed total.',
            ],
            [
                'question' => 'How much should I save before having a baby?',
                'answer' => 'A useful planning approach is remaining startup purchases, plus a few months of recurring baby expenses, plus any expected income drop during leave, minus savings you will have by the due date.',
            ],
            [
                'question' => 'What are typical newborn expenses in Canada?',
                'answer' => 'Common startup categories include a safe sleep setup, car seat, diapers, clothing, and feeding supplies. Many families also budget for a stroller or carrier. Gift and used items can reduce the cash needed.',
            ],
            [
                'question' => 'Does this include childcare?',
                'answer' => 'Only if you turn childcare on and enter a monthly amount. The calculator does not assume a national childcare rate or subsidy.',
            ],
            [
                'question' => 'Does everyone receive the maximum Canada Child Benefit?',
                'answer' => 'No. The '.$ccb['period_label'].' maximum for a child under 6 is a ceiling. Actual CCB depends on income and family circumstances. Enter your own estimate or use the CRA calculator.',
            ],
            [
                'question' => 'Can I bring over a parental-leave income change?',
                'answer' => 'Yes. After you run the parental leave calculator, you can carry the estimated monthly income reduction into this page for the current visit. It is not stored as an account or emailed.',
            ],
        ];
    }
}
