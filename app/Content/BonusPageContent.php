<?php

namespace App\Content;

use App\Support\Province;

class BonusPageContent
{
    /**
     * @return array<string, mixed>
     */
    public function national(): array
    {
        $year = config('tax.current_year');

        return [
            'h1' => 'Bonus Tax Calculator',
            'title' => 'Bonus Tax Calculator Canada '.$year.' | Paycheque.app',
            'description' => 'Estimate how much of a Canadian employment bonus you keep after income tax, CPP or QPP, and EI. Compare salary alone with salary plus bonus.',
            'intro' => 'A bonus is employment income. This calculator does not multiply the bonus by a flat tax rate. It runs the payroll engine on your salary, then on salary plus bonus, and shows the difference.',
            'sections' => [
                [
                    'heading' => 'How bonuses are taxed',
                    'copy' => 'A cash bonus is added to employment income for the year. It can increase federal and provincial income tax and, if you are still under the annual ceilings, CPP or QPP and EI (plus QPIP in Quebec).',
                ],
                [
                    'heading' => 'Withholding vs final tax',
                    'copy' => 'Employers often withhold tax on a bonus using a payroll method that can look harsher than the annual result. This page estimates the incremental annual liability, not the exact amount that will come off one bonus cheque.',
                ],
                [
                    'heading' => 'Contribution ceilings',
                    'copy' => 'Once you have already reached the CPP/QPP or EI maximum for the year, extra bonus income should not create more of those contributions. The existing payroll engine already applies those ceilings.',
                ],
            ],
            'faqs' => $this->faqs(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function province(Province $province): array
    {
        $year = config('tax.current_year');
        $name = $province->name();
        $contributions = $province->usesQpp()
            ? 'federal tax, Quebec provincial tax, QPP, EI, and QPIP'
            : 'federal tax, '.$province->adjective().' provincial or territorial tax, CPP, and EI';

        return [
            'h1' => $name.' Bonus Tax Calculator',
            'title' => $name.' Bonus Tax Calculator '.$year.' | Paycheque.app',
            'description' => "Estimate how much of a bonus you keep in {$name} after {$contributions}. Uses the same {$year} payroll engine as the paycheck calculator.",
            'intro' => "A bonus paid for work in {$name} is employment income. Enter your salary and bonus to see the estimated incremental tax and payroll contributions — not a flat bonus tax rate.",
            'sections' => [
                [
                    'heading' => 'Bonuses in '.$name,
                    'copy' => $province->usesQpp()
                        ? 'Quebec employees still pay federal tax (with the Quebec abatement), Revenu Québec provincial tax, QPP instead of CPP, a lower EI rate, and QPIP. A bonus can increase each of those until the contribution ceilings are reached.'
                        : "In {$name}, a bonus is added to the same employment income used for federal tax, provincial or territorial tax, CPP, and EI. If you are already at the CPP or EI maximum, the extra deductions are mostly income tax.",
                ],
                [
                    'heading' => 'What your employer may withhold',
                    'copy' => 'Payroll software may treat a bonus as a lump-sum or extra-period payment. The amount withheld on the cheque can be higher or lower than this annual estimate. The year-end T4 / Relevé 1 is what matters for your actual tax.',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Is a bonus taxed differently in '.$name.' than regular salary?',
                    'answer' => 'For annual tax, no — it is employment income. Withholding on the bonus payment itself can still look different from regular paycheques.',
                ],
                ...$this->faqs(),
            ],
        ];
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    private function faqs(): array
    {
        return [
            [
                'question' => 'How is the net bonus calculated?',
                'answer' => 'Net bonus is estimated take-home on salary plus bonus minus estimated take-home on salary alone.',
            ],
            [
                'question' => 'Will I pay more CPP or EI on a bonus?',
                'answer' => 'Only if you have not already reached the annual maximums. High earners who have already maxed CPP/QPP and EI should see mostly income tax on the extra amount. Quebec also uses QPIP.',
            ],
            [
                'question' => 'Why doesn’t this match my bonus cheque?',
                'answer' => 'Employers withhold using payroll formulas that can differ from the final annual tax on the combined income. This page estimates the annual incremental effect.',
            ],
        ];
    }
}
