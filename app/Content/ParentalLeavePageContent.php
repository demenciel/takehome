<?php

namespace App\Content;

class ParentalLeavePageContent
{
    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        $year = (int) config('benefits.year', config('tax.current_year'));

        return [
            'h1' => 'Canadian Parental Leave Calculator',
            'title' => 'Parental Leave Calculator Canada '.$year.' | Paycheque.app',
            'description' => 'Estimate maternity and parental leave income in Canada, including EI weekly benefits, standard vs extended leave, employer top-up, and household cash-flow.',
            'intro' => 'Estimate what your paycheque may look like during maternity or parental leave. The calculator uses '.$year.' federal EI rates for provinces and territories outside Québec, then compares that leave income with your regular take-home pay.',
            'sections' => [
                [
                    'heading' => 'How parental leave income is estimated',
                    'copy' => 'Federal EI maternity benefits pay 55% of average insurable weekly earnings for up to 15 weeks, and parental benefits pay either 55% (standard) or 33% (extended), each subject to a weekly maximum. This page does not treat salary as proof of eligibility. Service Canada still reviews insurable hours and your claim.',
                ],
                [
                    'heading' => 'Standard vs extended parental benefits',
                    'copy' => 'Standard parental benefits pay more per week for a shorter period. Extended parental benefits pay less per week for longer. The financially better path depends on your expenses, top-up, and how long you want to be off work. This calculator does not pick a winner.',
                ],
                [
                    'heading' => 'Employer top-ups',
                    'copy' => 'Some employers add a top-up so leave income reaches a share of regular salary, often for a set number of weeks. Plans differ. The default here is the common “top up to X%” interpretation. Enter your own percentage and duration, or leave top-up off.',
                ],
                [
                    'heading' => 'Québec uses QPIP, not federal EI',
                    'copy' => 'Most Québec births and adoptions are paid through the Québec Parental Insurance Plan. This calculator does not apply federal EI rates to Québec or invent QPIP amounts. If you select Québec, you will see your regular take-home estimate and a link to the official RQAP service.',
                ],
                [
                    'heading' => 'Household cash-flow during leave',
                    'copy' => 'If you enter a partner’s income and monthly expenses, the page estimates household income before and during leave and the amount your household may need to cover from savings. That is a planning figure, not financial advice.',
                ],
            ],
            'faqs' => $this->faqs(),
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    public function faqs(): array
    {
        return [
            [
                'question' => 'How much will I make on maternity leave in Canada?',
                'answer' => 'Federal EI maternity benefits are 55% of your average insurable weekly earnings, up to the published weekly maximum, for up to 15 weeks. The amount is an estimate until Service Canada decides your claim.',
            ],
            [
                'question' => 'What is the difference between 12-month and 18-month parental leave?',
                'answer' => 'Standard parental benefits last up to 40 weeks shared (35 for one parent) at 55%. Extended parental benefits last up to 69 weeks shared (61 for one parent) at 33%. Maternity weeks, if claimed, are in addition to parental weeks.',
            ],
            [
                'question' => 'Does this calculator confirm I am eligible?',
                'answer' => 'No. Salary is used only to estimate insurable earnings. Eligibility depends on insurable hours, your work history, and Service Canada’s decision.',
            ],
            [
                'question' => 'Are EI maternity and parental benefits taxable?',
                'answer' => 'Yes. EI benefits are taxable income. This page shows estimated benefit amounts before tax unless a figure is labelled as regular employment take-home.',
            ],
            [
                'question' => 'How does an employer top-up work?',
                'answer' => 'A top-up is extra pay from your employer, often bringing combined EI plus top-up toward a percentage of regular salary for a limited number of weeks. Your employment contract or policy controls the amount.',
            ],
            [
                'question' => 'How does parental leave work in Québec?',
                'answer' => 'Québec maternity, paternity, parental, and adoption benefits are paid through QPIP, not federal EI. Use the official RQAP estimator rather than a federal EI estimate.',
            ],
        ];
    }
}
