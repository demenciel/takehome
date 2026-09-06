<?php

namespace App\Content;

use App\Support\Province;

class RaisePageContent
{
    /**
     * @return array<string, mixed>
     */
    public function national(): array
    {
        $year = config('tax.current_year');

        return [
            'h1' => 'Raise Calculator',
            'title' => 'Raise Calculator Canada — How Much of Your Raise Do You Keep? | Paycheque.app',
            'description' => 'See how much of a Canadian salary increase you keep after income tax, CPP or QPP, and EI. Compare take-home pay before and after the raise.',
            'intro' => 'A raise increases gross pay, but not dollar-for-dollar in your bank account. This calculator runs the payroll engine on your current salary and your new salary, then shows the extra take-home by month, biweekly cheque, and week.',
            'sections' => [
                [
                    'heading' => 'How the net raise is calculated',
                    'copy' => 'Net raise is estimated annual take-home on the new salary minus take-home on the current salary. Extra monthly, biweekly, and weekly amounts divide that annual difference by 12, 26, and 52.',
                ],
                [
                    'heading' => 'Why you do not keep 100%',
                    'copy' => 'The extra income can attract more federal and provincial tax. If you are still below the ceilings, CPP or QPP and EI (and QPIP in Quebec) can also rise. Crossing a bracket or the CPP2 threshold changes the share you keep.',
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

        return [
            'h1' => $name.' Raise Calculator',
            'title' => $name.' Raise Calculator '.$year.' — How Much Do You Keep? | Paycheque.app',
            'description' => "Estimate how much of a salary increase you keep in {$name} after {$year} income tax and payroll contributions.",
            'intro' => $province->usesQpp()
                ? 'In Quebec, a raise can change federal tax, Revenu Québec tax, QPP, EI, and QPIP. Compare your current and new salary to see the extra take-home.'
                : "In {$name}, a raise is taxed through the same federal and provincial or territorial payroll rules as your current salary. Compare both sides to see how much of the increase you keep.",
            'sections' => [
                [
                    'heading' => 'What changes in '.$name,
                    'copy' => $province->usesQpp()
                        ? 'Quebec uses QPP and QPIP instead of the rest-of-Canada CPP/EI pairing, plus a federal abatement. A raise that crosses the QPP or QPP2 range will not increase those contributions further once the annual maximums are reached.'
                        : "Provincial brackets and credits in {$name} affect how much of the raise remains after tax. CPP and EI increase only until their annual maximums. The table on this page uses a worked {$name} example so you can see the before/after split.",
                ],
            ],
            'faqs' => [
                [
                    'question' => 'Does a raise push me into a higher tax bracket in '.$name.'?',
                    'answer' => 'Only the dollars above a bracket threshold are taxed at the higher rate. This calculator uses the full payroll estimate, so bracket crossings are already in the before/after difference.',
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
                'question' => 'How much of my raise will I actually keep?',
                'answer' => 'The keep percentage is net raise divided by gross raise. It is based on two annual payroll estimates, not a single marginal rate applied to the raise.',
            ],
            [
                'question' => 'Can I enter a raise as a percentage?',
                'answer' => 'Yes. Choose Raise % and the calculator converts it into a new annual salary before running the comparison.',
            ],
            [
                'question' => 'Is this the same as a bonus calculator?',
                'answer' => 'A raise changes ongoing salary. A bonus is usually a one-time amount added to this year’s income. Use the bonus tax calculator for that case.',
            ],
        ];
    }
}
