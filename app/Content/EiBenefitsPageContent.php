<?php

namespace App\Content;

class EiBenefitsPageContent
{
    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        $year = (int) config('benefits.year', config('tax.current_year'));
        $maternity = config('benefits.years.'.$year.'.ei.maternity');
        $standard = config('benefits.years.'.$year.'.ei.standard_parental');
        $extended = config('benefits.years.'.$year.'.ei.extended_parental');

        return [
            'h1' => 'EI Maternity & Parental Benefits Calculator',
            'title' => 'EI Maternity and Parental Benefits Calculator '.$year.' | Paycheque.app',
            'description' => 'Estimate '.$year.' EI maternity and parental benefits in Canada, including weekly maxima, standard vs extended rates, shared weeks, and total benefits.',
            'intro' => 'Use this page when you want the federal EI maternity or parental benefit estimate itself — weekly amount, rate, maximum, and total — without a full household budget.',
            'verified_label' => 'Rates verified for '.$year,
            'sections' => [
                [
                    'heading' => 'What is the maximum EI maternity benefit in '.$year.'?',
                    'copy' => 'The '.$year.' maximum weekly EI maternity benefit is $'.number_format((float) $maternity['max_weekly'], 0).'. Maternity benefits pay 55% of average insurable weekly earnings for up to '.(int) $maternity['max_weeks'].' weeks. Most people receive less than the maximum if their insurable earnings are below the yearly ceiling.',
                ],
                [
                    'heading' => 'What is the maximum EI parental benefit in '.$year.'?',
                    'copy' => 'Standard parental benefits have the same '.$year.' weekly maximum as maternity: $'.number_format((float) $standard['max_weekly'], 0).' at 55%. Extended parental benefits have a lower weekly maximum of $'.number_format((float) $extended['max_weekly'], 0).' at 33%.',
                ],
                [
                    'heading' => 'What is the difference between standard and extended parental benefits?',
                    'copy' => 'Standard parental benefits pay 55% for up to '.(int) $standard['shared_max_weeks'].' weeks shared, with one parent usually limited to '.(int) $standard['individual_max_weeks'].' weeks, generally within '.(int) $standard['window_weeks'].' weeks of birth or adoption. Extended parental benefits pay 33% for up to '.(int) $extended['shared_max_weeks'].' weeks shared, with one parent usually limited to '.(int) $extended['individual_max_weeks'].' weeks, generally within '.(int) $extended['window_weeks'].' weeks.',
                ],
                [
                    'heading' => 'Can both parents receive parental benefits?',
                    'copy' => 'Yes. Parental weeks are a shared pool. In '.$year.', standard parental weeks total '.(int) $standard['shared_max_weeks'].' and extended weeks total '.(int) $extended['shared_max_weeks'].'. One parent cannot usually take the entire pool.',
                ],
                [
                    'heading' => 'Can parents take parental benefits at the same time?',
                    'copy' => 'Yes. Parents can take parental benefits at the same time or at different times, as long as the shared week limits and claiming window are respected. Service Canada explains the current claiming rules.',
                ],
                [
                    'heading' => 'Can I switch from standard to extended later?',
                    'copy' => 'Once you choose standard or extended parental benefits, that choice generally applies to the claim. Confirm the current rule with Service Canada before you apply. This calculator does not change a submitted claim.',
                ],
                [
                    'heading' => 'Is EI maternity/parental income taxable?',
                    'copy' => 'Yes. EI benefits are taxable. Tax may be withheld from the benefit, and the amount still belongs on your tax return. Figures on this page are estimated benefits, not after-tax take-home.',
                ],
                [
                    'heading' => 'How does an employer top-up work?',
                    'copy' => 'A top-up is employer-paid income on top of EI, often for a limited number of weeks. It is not part of the federal EI calculation. Use the parental leave calculator if you want to include a top-up in a household estimate.',
                ],
                [
                    'heading' => 'How does parental leave work in Québec?',
                    'copy' => 'Québec administers maternity, paternity, parental, and adoption benefits through QPIP. Federal EI maternity and parental rules do not apply to most Québec claims. This page will not produce a federal EI estimate for Québec.',
                ],
            ],
            'faqs' => $this->faqs($year, $maternity, $standard, $extended),
        ];
    }

    /**
     * @param  array<string, mixed>  $maternity
     * @param  array<string, mixed>  $standard
     * @param  array<string, mixed>  $extended
     * @return list<array{question: string, answer: string}>
     */
    public function faqs(int $year, array $maternity, array $standard, array $extended): array
    {
        return [
            [
                'question' => 'What is the maximum EI maternity benefit in '.$year.'?',
                'answer' => 'The published weekly maximum is $'.number_format((float) $maternity['max_weekly'], 0).' for up to '.(int) $maternity['max_weeks'].' weeks at 55% of insurable weekly earnings.',
            ],
            [
                'question' => 'What is the maximum EI parental benefit in '.$year.'?',
                'answer' => 'Standard parental benefits max out at $'.number_format((float) $standard['max_weekly'], 0).' a week. Extended parental benefits max out at $'.number_format((float) $extended['max_weekly'], 0).' a week.',
            ],
            [
                'question' => 'What’s the difference between standard and extended parental benefits?',
                'answer' => 'Standard pays 55% for fewer weeks. Extended pays 33% for more weeks. Neither option is always better financially.',
            ],
            [
                'question' => 'Can both parents receive parental benefits?',
                'answer' => 'Yes. The weeks are shared. One parent cannot usually receive the full shared maximum.',
            ],
            [
                'question' => 'Can parents take parental benefits at the same time?',
                'answer' => 'Yes, they can overlap or be taken separately, subject to the shared week limit and claiming window.',
            ],
            [
                'question' => 'Can I switch from standard to extended later?',
                'answer' => 'The standard or extended choice generally applies to the claim. Check with Service Canada before you apply.',
            ],
            [
                'question' => 'Is EI maternity/parental income taxable?',
                'answer' => 'Yes. EI benefits are taxable income. This calculator shows estimated benefits, not after-tax pay.',
            ],
            [
                'question' => 'How does parental leave work in Québec?',
                'answer' => 'Québec uses QPIP rather than federal EI for most maternity and parental benefits. Use the official RQAP service.',
            ],
        ];
    }
}
