<?php

namespace App\Content;

use App\Services\Overtime\OvertimeRuleProvider;
use App\Support\Province;

class OvertimePageContent
{
    public function __construct(private OvertimeRuleProvider $rules) {}

    /**
     * @return array<string, mixed>
     */
    public function national(): array
    {
        $year = config('tax.current_year');

        return [
            'h1' => 'Canadian Overtime Pay Calculator',
            'title' => 'Canadian Overtime Pay Calculator '.$year.' | Paycheque.app',
            'description' => 'Estimate overtime pay and how much you may keep after tax, CPP or QPP, and EI. Rules follow official provincial employment standards, not a single national overtime formula.',
            'intro' => 'Overtime is not the same in every province. Enter your hourly wage and hours to estimate regular pay, overtime pay, and the after-tax value of that overtime using the same payroll engine as the paycheck calculator.',
            'sections' => [
                [
                    'heading' => 'How overtime is calculated here',
                    'copy' => 'Regular hours are paid at your hourly wage. Overtime hours use that jurisdiction’s standard premium — usually 1.5×, and 2× after 12 hours in a day in British Columbia. New Brunswick’s statutory floor is 1.5× the minimum wage, not automatically 1.5× your regular rate.',
                ],
                [
                    'heading' => 'How after-tax overtime is estimated',
                    'copy' => 'The calculator assumes the same hours repeat every pay period for a full year, then compares take-home pay with and without the overtime. That difference is the estimated after-tax value. It is not overtime pay multiplied by a guessed tax rate.',
                ],
                [
                    'heading' => 'What this does not cover',
                    'copy' => 'Managers, some professions, averaging agreements, collective agreements, and federally regulated workplaces can follow different rules. This tool models the standard employment-standards default, not every exemption.',
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
        $rules = $this->rules->for($province);
        $name = $province->name();
        $threshold = $rules['weekly_threshold'];
        $daily = $rules['daily_threshold'];

        $how = $daily
            ? "Most employees in {$name} become entitled to overtime after {$daily} hours in a day or {$threshold} hours in a week, paid at {$rules['multiplier']}× the regular wage."
            : "Most employees in {$name} become entitled to overtime after the standard weekly threshold of {$threshold} hours, paid at {$rules['multiplier']}×.";

        return [
            'h1' => $name.' Overtime Pay Calculator',
            'title' => $name.' Overtime Pay Calculator '.$year.' | Paycheque.app',
            'description' => "Estimate {$name} overtime pay after the standard {$threshold}-hour weekly threshold, then see how much of that overtime may remain after payroll deductions.",
            'intro' => $how.' Enter your hourly rate and hours worked to estimate gross overtime and what may remain after income tax, '.($province->usesQpp() ? 'QPP, EI, and QPIP' : 'CPP, and EI').'.',
            'rules' => $rules,
            'sections' => [
                [
                    'heading' => 'Standard overtime in '.$name,
                    'copy' => $rules['summary'],
                ],
                [
                    'heading' => 'After-tax overtime',
                    'copy' => 'The tax estimate annualizes the pay period you enter and runs the '.$name.' payroll calculation twice — with overtime and without it. The difference is how much of the overtime this calculator thinks you keep. Employer withholding on one cheque can still differ.',
                ],
                [
                    'heading' => 'Exemptions and contracts',
                    'copy' => $rules['exceptions'].' Collective agreements and employment contracts can also set a different (usually more generous) overtime premium.',
                ],
            ],
            'faqs' => [
                [
                    'question' => 'When does overtime start in '.$name.'?',
                    'answer' => $daily
                        ? "The standard rule is after {$daily} hours in a day or {$threshold} hours in a week. Some jobs are exempt or use averaging."
                        : "The standard rule is after {$threshold} hours in a work week. Some jobs are exempt or use averaging agreements.",
                ],
                [
                    'question' => 'Is overtime always time and a half in '.$name.'?',
                    'answer' => $rules['rate_basis'] === 'greater_of_regular_or_min_ot'
                        ? 'The Employment Standards Act sets a minimum of 1.5× the minimum wage. Many contracts pay 1.5× the regular wage when that is higher. Use the optional checkbox if your contract pays the higher premium.'
                        : ($rules['daily_double_threshold']
                            ? "Hours over {$rules['daily_threshold']} in a day or {$threshold} in a week are usually 1.5×. Hours over {$rules['daily_double_threshold']} in a day are 2×."
                            : "The standard premium is {$rules['multiplier']}× the regular wage. Contracts can pay more."),
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
                'question' => 'How much of my overtime do I actually keep?',
                'answer' => 'This calculator estimates that by comparing annual take-home pay with and without the overtime hours. The gap is the after-tax value, after income tax and payroll contributions.',
            ],
            [
                'question' => 'Does every Canadian province use a 40-hour overtime week?',
                'answer' => 'No. Ontario and New Brunswick generally use 44 hours. Nova Scotia and Prince Edward Island generally use 48. Several jurisdictions also have daily thresholds.',
            ],
            [
                'question' => 'Is this legal advice?',
                'answer' => 'No. Employment-standards rules have exemptions. Use the official source linked on the page, or an employment-standards office, for your situation.',
            ],
        ];
    }
}
