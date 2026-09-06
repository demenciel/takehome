<?php

namespace App\Services\Seo;

use App\Calculators\Results\CalculatorResult;
use App\Services\Tax\TaxFreshness;
use App\Support\HourlyConversion;
use App\Support\Money;
use App\Support\PayFrequency;
use App\Support\Province;
use Carbon\CarbonImmutable;
use Throwable;

class AnswerSummaryBuilder
{
    public function __construct(private TaxFreshness $freshness) {}

    /**
     * @param  list<array{label: string, value: string}>  $facts
     * @param  list<array{title: string, url: string}>  $sources
     * @return array{
     *     question: string,
     *     answer: string,
     *     facts: list<array{label: string, value: string}>,
     *     jurisdiction: string|null,
     *     year: int,
     *     updated: string,
     *     methodology_url: string,
     *     sources: list<array{title: string, url: string}>
     * }
     */
    public function make(
        string $question,
        string $answer,
        array $facts = [],
        ?string $jurisdiction = null,
        array $sources = [],
        ?string $updated = null,
    ): array {
        return [
            'question' => $question,
            'answer' => $answer,
            'facts' => $facts,
            'jurisdiction' => $jurisdiction,
            'year' => $this->freshness->year(),
            'updated' => $this->formatDate($updated) ?? $this->freshness->lastUpdatedLabel(),
            'methodology_url' => route('methodology'),
            'sources' => $sources,
        ];
    }

    /**
     * @param  list<array{frequency: PayFrequency, net: Money}>  $frequencies
     * @return array<string, mixed>
     */
    public function salary(Province $province, int $salary, CalculatorResult $result, array $frequencies, Money $hourly): array
    {
        $net = $result->metrics['net_annual'];
        $monthly = $this->frequencyNet($frequencies, PayFrequency::Monthly) ?? $net->divideBy(12);
        $biweekly = $this->frequencyNet($frequencies, PayFrequency::Biweekly) ?? $net->divideBy(26);
        $weekly = $this->frequencyNet($frequencies, PayFrequency::Weekly) ?? $net->divideBy(52);
        $gross = '$'.number_format($salary);
        $name = $province->name();

        return $this->make(
            question: "How much is an {$gross} salary after tax in {$name}?",
            answer: "An {$gross} annual salary in {$name} results in an estimated take-home pay of {$net->format()} per year, or about {$monthly->format()} per month, based on {$this->freshness->year()} payroll rules.",
            facts: $this->payrollFacts($province, Money::fromDollars($salary), $result, $monthly, $biweekly, $weekly, $hourly),
            jurisdiction: $name,
            sources: $this->taxSources($province),
        );
    }

    /**
     * @param  array{salary: int, net_annual: Money, effective_tax_rate: string}|null  $example
     * @return array<string, mixed>
     */
    public function province(Province $province, ?array $example): array
    {
        $name = $province->name();
        $year = $this->freshness->year();

        if ($example) {
            $gross = '$'.number_format($example['salary']);
            $answer = "In {$name}, a {$gross} salary has an estimated take-home of {$example['net_annual']->format()} per year using {$year} payroll rules. Take-home is salary minus federal tax, {$province->adjective()} tax, ".($province->usesQpp() ? 'QPP, EI, and QPIP' : 'CPP, and EI').'.';
            $facts = [
                ['label' => 'Example gross salary', 'value' => $gross],
                ['label' => 'Estimated annual take-home', 'value' => $example['net_annual']->format()],
                ['label' => 'Effective tax rate', 'value' => $example['effective_tax_rate'].'%'],
                ['label' => 'Payroll year', 'value' => (string) $year],
            ];
        } else {
            $answer = "{$name} take-home pay is estimated from federal tax, {$province->adjective()} tax, ".($province->usesQpp() ? 'QPP, EI, and QPIP' : 'CPP, and EI').". Enter a salary to see the {$year} estimate for a full-year employee claiming the basic personal amount.";
            $facts = [
                ['label' => 'Payroll year', 'value' => (string) $year],
                ['label' => 'Claim code used', 'value' => '1 (basic personal amount)'],
            ];
        }

        return $this->make(
            question: "How is take-home pay calculated in {$name}?",
            answer: $answer,
            facts: $facts,
            jurisdiction: $name,
            sources: $this->taxSources($province),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function canada(): array
    {
        return $this->make(
            question: 'How is take-home pay calculated in Canada?',
            answer: 'Canadian take-home pay is gross salary minus federal income tax, provincial or territorial tax, CPP or QPP, and EI. Quebec also deducts QPIP and uses Revenu Québec provincial tax. Choose a province — this calculator does not assume one.',
            facts: [
                ['label' => 'Payroll year', 'value' => (string) $this->freshness->year()],
                ['label' => 'Federal formula', 'value' => 'CRA T4127 Option 1'],
                ['label' => 'Claim code used', 'value' => '1 (basic personal amount)'],
            ],
            jurisdiction: 'Canada',
            sources: $this->taxSources(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function hub(string $question, string $answer): array
    {
        return $this->make(
            question: $question,
            answer: $answer,
            facts: [
                ['label' => 'Payroll year', 'value' => (string) $this->freshness->year()],
                ['label' => 'Federal formula', 'value' => 'CRA T4127 Option 1'],
                ['label' => 'Claim code used', 'value' => '1 (basic personal amount)'],
            ],
            jurisdiction: 'Canada',
            sources: $this->taxSources(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function conversion(string $mode): array
    {
        $hours = HourlyConversion::hoursPerYear();
        $assumption = HourlyConversion::assumptionLabel();

        if ($mode === 'salary_to_hourly') {
            $hourly = HourlyConversion::hourlyFromAnnual(Money::fromDollars(80000));

            return $this->make(
                question: 'What is an $80,000 salary per hour?',
                answer: "Using {$assumption}, $80,000 is about {$hourly->format()}/hour before tax. That is a conversion, not a contracted wage or take-home pay.",
                facts: [
                    ['label' => 'Example salary', 'value' => '$80,000'],
                    ['label' => 'Hourly equivalent (gross)', 'value' => $hourly->format()],
                    ['label' => 'Hours assumption', 'value' => number_format($hours).' hours/year'],
                ],
                jurisdiction: 'Canada',
            );
        }

        return $this->make(
            question: 'How do I convert hourly pay to an annual salary?',
            answer: "Multiply hourly wage × hours per week × weeks per year. The default on this site is {$assumption}. That is a gross conversion — tax still depends on province and the annual total.",
            facts: [
                ['label' => 'Default hours/week', 'value' => (string) config('tax.hours_per_week', 40)],
                ['label' => 'Default weeks/year', 'value' => (string) config('tax.weeks_per_year', 52)],
                ['label' => 'Default hours/year', 'value' => number_format($hours)],
            ],
            jurisdiction: 'Canada',
        );
    }

    /**
     * @param  array<string, mixed>  $example
     * @param  array<string, mixed>|null  $rules
     * @return array<string, mixed>
     */
    public function overtime(?Province $province, array $example, ?array $rules = null): array
    {
        $place = $province?->name() ?? 'Canada';
        $grossOt = $example['overtime_pay']->format();
        $netOt = $example['after_tax_overtime']->format();
        $rate = $example['overtime_rate']->format();
        $rules ??= $example['rules'] ?? [];
        $threshold = $rules['weekly_threshold'] ?? null;
        $multiplier = $rules['multiplier'] ?? null;
        $daily = $rules['daily_threshold'] ?? null;

        $thresholdText = $threshold
            ? "most employees qualify for overtime after the standard weekly threshold of {$threshold} hours"
            : 'overtime thresholds vary by province';

        $answer = $province
            ? "In {$place}, {$thresholdText}. At $30/hour with 8 overtime hours, gross overtime is {$grossOt} and estimated after-tax overtime is {$netOt} based on {$this->freshness->year()} payroll rules. Occupations and contracts can differ."
            : "Overtime rules differ by province. As an Ontario example at $30/hour with 8 overtime hours, gross overtime is {$grossOt} and estimated after-tax overtime is {$netOt}. After-tax overtime is take-home with overtime minus take-home without it.";

        $facts = [
            ['label' => 'Example hourly wage', 'value' => '$30.00'],
            ['label' => 'Example overtime hours', 'value' => '8'],
            ['label' => 'Overtime rate', 'value' => $rate.'/hour'],
            ['label' => 'Gross overtime', 'value' => $grossOt],
            ['label' => 'Estimated after-tax overtime', 'value' => $netOt],
            ['label' => 'Standard weekly threshold', 'value' => $threshold ? $threshold.' hours' : 'Varies by province'],
            ['label' => 'Standard multiplier', 'value' => $multiplier ? $multiplier.'×' : 'Usually 1.5×'],
            ['label' => 'Daily overtime', 'value' => $daily ? $daily.' hours' : 'No general daily threshold'],
        ];

        $source = $rules['source'] ?? null;
        $sources = $source ? [['title' => $source['title'], 'url' => $source['url']]] : [];

        return $this->make(
            question: "How much overtime pay do I keep after tax in {$place}?",
            answer: $answer,
            facts: $facts,
            jurisdiction: $place,
            sources: $sources,
            updated: $example['retrieved_date'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $example
     * @return array<string, mixed>
     */
    public function bonus(?Province $province, array $example): array
    {
        $place = $province?->name() ?? 'Canada';
        $net = $example['net_annual_delta']->format();
        $keep = $example['keep_percent'];
        $tax = $example['tax_delta']->format();

        return $this->make(
            question: "How much of a \$10,000 bonus do I keep after tax in {$place}?",
            answer: "On an \$80,000 salary, a \$10,000 bonus in {$place} has an estimated net of {$net} ({$keep}% kept) after about {$tax} in extra income tax plus any remaining CPP/QPP or EI/QPIP. Employer withholding on the bonus cheque can differ from this annual estimate.",
            facts: [
                ['label' => 'Gross bonus', 'value' => '$10,000.00'],
                ['label' => 'Estimated additional tax', 'value' => $tax],
                ['label' => 'CPP/QPP impact', 'value' => $example['pension_delta']->format()],
                ['label' => 'EI/QPIP impact', 'value' => $example['insurance_delta']->format()],
                ['label' => 'Estimated net bonus', 'value' => $net],
                ['label' => 'Share kept', 'value' => $keep.'%'],
            ],
            jurisdiction: $place,
            sources: $this->taxSources($province),
        );
    }

    /**
     * @param  array<string, mixed>  $example
     * @return array<string, mixed>
     */
    public function raise(?Province $province, array $example): array
    {
        $place = $province?->name() ?? 'Canada';
        $net = $example['net_annual_delta'];
        $keep = $example['keep_percent'];

        return $this->make(
            question: "How much of a \$10,000 raise do I actually keep in {$place}?",
            answer: "Moving from \$75,000 to \$85,000 in {$place} is a \$10,000 raise. Estimated extra take-home is {$net->format()} a year, or {$net->divideBy(12)->format()} a month ({$keep}% of the raise), using {$this->freshness->year()} payroll rules.",
            facts: [
                ['label' => 'Gross annual raise', 'value' => $example['gross_delta']->format()],
                ['label' => 'Net annual increase', 'value' => $net->format()],
                ['label' => 'Monthly increase', 'value' => $net->divideBy(12)->format()],
                ['label' => 'Biweekly increase', 'value' => $net->divideBy(26)->format()],
                ['label' => 'Share of raise kept', 'value' => $keep.'%'],
            ],
            jurisdiction: $place,
            sources: $this->taxSources($province),
        );
    }

    /**
     * @param  array<string, mixed>  $example
     * @return array<string, mixed>
     */
    public function military(array $example): array
    {
        $pay = $example['pay'];
        $monthlyBase = $pay['rate']->format();
        $takeHome = $example['payroll']->metrics['net_annual']->divideBy(12)->format();
        $sources = $example['sources']['sources'] ?? [];
        $official = array_values(array_filter(
            $sources,
            fn (array $source) => str_contains(strtolower($source['title']), 'regular force'),
        ));

        return $this->make(
            question: 'How much does a Regular Force Corporal earn and take home?',
            answer: "A Regular Force {$pay['rank_short']} at {$pay['increment_label']} earns {$monthlyBase}/month in base pay under the current published DND table. Estimated take-home in Ontario is approximately {$takeHome}/month before excluded allowances and benefits.",
            facts: [
                ['label' => 'Component', 'value' => $example['component_label']],
                ['label' => 'Rank', 'value' => $pay['rank_name']],
                ['label' => 'Pay increment', 'value' => $pay['increment_label']],
                ['label' => 'Official monthly base pay', 'value' => $monthlyBase],
                ['label' => 'Pay-table effective date', 'value' => $this->formatDate($example['effective_date']) ?? $example['edition']],
                ['label' => 'Annualized base pay', 'value' => $example['base_annual']->format()],
                ['label' => 'Estimated annual take-home', 'value' => $example['payroll']->metrics['net_annual']->format()],
                ['label' => 'Pension included', 'value' => $example['pension']['included'] ? 'Yes (Regular Force estimate)' : 'No'],
                ['label' => 'Allowances included', 'value' => 'No, unless entered'],
            ],
            jurisdiction: 'Ontario example',
            sources: $official !== [] ? $official : array_slice($sources, 0, 2),
            updated: $example['retrieved_date'] ?? null,
        );
    }

    /**
     * @param  list<array{frequency: PayFrequency, net: Money}>  $frequencies
     * @return list<array{question: string, answer: string}>
     */
    public function salaryFaqs(Province $province, int $salary, CalculatorResult $result, array $frequencies, Money $hourly, string $explanation): array
    {
        $gross = '$'.number_format($salary);
        $name = $province->name();
        $monthly = $this->frequencyNet($frequencies, PayFrequency::Monthly) ?? $result->metrics['net_annual']->divideBy(12);
        $biweekly = $this->frequencyNet($frequencies, PayFrequency::Biweekly) ?? $result->metrics['net_annual']->divideBy(26);
        $pension = $province->usesQpp() ? 'QPP' : 'CPP';
        $second = $result->metrics['cpp2']->isPositive()
            ? ' plus '.$result->metrics['cpp2']->format().' '.($province->usesQpp() ? 'QPP2' : 'CPP2')
            : '';
        $qpip = $result->metrics['qpip']->isPositive() ? ' QPIP is '.$result->metrics['qpip']->format().'.' : '';
        $tax = $result->metrics['federal_tax']->add($result->metrics['provincial_tax']);

        return [
            [
                'question' => "How much is {$gross} after tax in {$name}?",
                'answer' => $explanation,
            ],
            [
                'question' => "What is {$gross} per month after tax in {$name}?",
                'answer' => "Estimated monthly take-home is {$monthly->format()} on a {$gross} salary in {$name}.",
            ],
            [
                'question' => "What is {$gross} biweekly after tax in {$name}?",
                'answer' => "Estimated biweekly take-home is {$biweekly->format()}, which is the annual net divided by 26.",
            ],
            [
                'question' => 'How much income tax is paid?',
                'answer' => "Estimated income tax is {$tax->format()} ({$result->metrics['federal_tax']->format()} federal and {$result->metrics['provincial_tax']->format()} {$province->adjective()}).",
            ],
            [
                'question' => "How much {$pension} and EI are deducted?",
                'answer' => "{$pension} is {$result->metrics['cpp']->format()}{$second}. EI is {$result->metrics['ei']->format()}.{$qpip}",
            ],
            [
                'question' => "What is {$gross} per hour?",
                'answer' => 'Using '.HourlyConversion::assumptionLabel().", {$gross} is about {$hourly->format()}/hour before tax. That is a conversion, not a contracted wage.",
            ],
        ];
    }

    /**
     * @param  list<array{question: string, answer: string}>  $base
     * @param  array<string, mixed>  $example
     * @param  array<string, mixed>|null  $rules
     * @return list<array{question: string, answer: string}>
     */
    public function overtimeFaqs(array $base, array $example, ?Province $province, ?array $rules = null): array
    {
        $rules ??= $example['rules'] ?? [];
        $exceptions = $rules['exceptions'] ?? 'Managers, some professions, averaging agreements, collective agreements, and federally regulated workplaces can differ.';

        return $this->mergeFaqs([
            [
                'question' => 'How much do I keep after tax?',
                'answer' => "In the $30/hour example with 8 overtime hours, estimated after-tax overtime is {$example['after_tax_overtime']->format()} per week using {$this->freshness->year()} payroll rules.",
            ],
            [
                'question' => 'Is overtime taxed differently?',
                'answer' => 'No. Overtime is employment income. Extra hours can increase income tax and remaining CPP/QPP or EI, so the amount you keep is less than the gross overtime premium.',
            ],
            [
                'question' => 'Are there overtime exemptions?',
                'answer' => $exceptions.' This calculator models the standard employment-standards default, not every exemption.',
            ],
        ], $base);
    }

    /**
     * @param  list<array{question: string, answer: string}>  $base
     * @param  array<string, mixed>  $example
     * @return list<array{question: string, answer: string}>
     */
    public function bonusFaqs(array $base, array $example, ?Province $province): array
    {
        $place = $province?->name() ?? 'Canada';

        return $this->mergeFaqs([
            [
                'question' => "How much of my bonus do I keep in {$place}?",
                'answer' => "On the $80,000 + $10,000 example, estimated net bonus is {$example['net_annual_delta']->format()} ({$example['keep_percent']}% kept) after extra income tax and any remaining CPP/QPP or EI/QPIP.",
            ],
            [
                'question' => 'Are bonuses taxed differently in Canada?',
                'answer' => 'For annual tax, no — a cash bonus is employment income. Employer withholding on the bonus cheque can still look higher than the final annual liability this calculator estimates.',
            ],
            [
                'question' => 'Why can bonus withholding look high?',
                'answer' => 'Payroll software may treat a bonus as a lump-sum or extra-period payment. The amount withheld on one cheque can differ from the year-end tax on salary plus bonus.',
            ],
            [
                'question' => 'Does CPP or EI apply to bonuses?',
                'answer' => 'Yes, until the annual ceilings are reached. High earners who have already maxed CPP/QPP and EI should see mostly income tax on the extra amount. Quebec also uses QPIP.',
            ],
        ], $base);
    }

    /**
     * @param  list<array{question: string, answer: string}>  $base
     * @param  array<string, mixed>  $example
     * @return list<array{question: string, answer: string}>
     */
    public function raiseFaqs(array $base, array $example, ?Province $province): array
    {
        $place = $province?->name() ?? 'Canada';
        $net = $example['net_annual_delta'];

        return $this->mergeFaqs([
            [
                'question' => "How much of my raise do I keep in {$place}?",
                'answer' => "On the $75,000 to $85,000 example, estimated extra take-home is {$net->format()} a year ({$example['keep_percent']}% of the $10,000 raise).",
            ],
            [
                'question' => 'What is the monthly net increase?',
                'answer' => "Estimated extra monthly take-home is {$net->divideBy(12)->format()} on that $10,000 raise example.",
            ],
            [
                'question' => 'What is the biweekly net increase?',
                'answer' => "Estimated extra biweekly take-home is {$net->divideBy(26)->format()}, which is the annual net increase divided by 26.",
            ],
            [
                'question' => 'Does a raise change CPP or EI?',
                'answer' => 'It can, until the annual maximums are reached. Crossing a bracket or the CPP2/QPP2 range also changes the share you keep. The before/after table already includes those ceilings.',
            ],
        ], $base);
    }

    /**
     * @param  list<array{question: string, answer: string}>  $base
     * @param  array<string, mixed>  $example
     * @return list<array{question: string, answer: string}>
     */
    public function militaryFaqs(array $base, array $example): array
    {
        $pay = $example['pay'];
        $monthly = $pay['rate']->format();
        $net = $example['payroll']->metrics['net_annual']->format();

        return $this->mergeFaqs([
            [
                'question' => 'How much does this rank earn?',
                'answer' => "A Regular Force {$pay['rank_short']} at {$pay['increment_label']} earns {$monthly}/month in official base pay ({$example['edition']}), or {$example['base_annual']->format()} annualized.",
            ],
            [
                'question' => 'What is the monthly base pay?',
                'answer' => "The official monthly base pay for this example is {$monthly}. Allowances are excluded unless you enter them.",
            ],
            [
                'question' => 'Is pension included?',
                'answer' => $example['pension']['included']
                    ? 'Yes for Regular Force. The estimate uses Treasury Board 2026 contribution rates. Members with 35 years of pensionable service are not modeled.'
                    : 'No. Reserve Force pension is not estimated.',
            ],
            [
                'question' => 'Are allowances included?',
                'answer' => 'No, unless entered. PLD/CFHD, LDA, sea duty, aircrew, deployment, and other CAF benefits are omitted from the published rank table.',
            ],
            [
                'question' => 'What deductions are estimated?',
                'answer' => "Income tax, CPP or QPP, EI, and QPIP in Quebec, plus Regular Force pension when applicable. Estimated annual take-home for this Ontario Corporal example is {$net}.",
            ],
        ], $base);
    }

    /**
     * @param  list<array{salary: int}>  $examples
     * @return array<string, mixed>|null
     */
    public function featuredExample(array $examples, int $preferred = 80000): ?array
    {
        foreach ($examples as $example) {
            if (($example['salary'] ?? null) === $preferred) {
                return $example;
            }
        }

        return $examples[0] ?? null;
    }

    /**
     * @param  list<array{question: string, answer: string}>  $preferred
     * @param  list<array{question: string, answer: string}>  $base
     * @return list<array{question: string, answer: string}>
     */
    private function mergeFaqs(array $preferred, array $base): array
    {
        $merged = [];

        foreach ([...$preferred, ...$base] as $faq) {
            $merged[$faq['question']] = $faq;
        }

        return array_slice(array_values($merged), 0, 8);
    }

    /**
     * @param  list<array{frequency: PayFrequency, net: Money}>  $frequencies
     */
    private function frequencyNet(array $frequencies, PayFrequency $wanted): ?Money
    {
        foreach ($frequencies as $row) {
            if ($row['frequency'] === $wanted) {
                return $row['net'];
            }
        }

        return null;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function payrollFacts(
        Province $province,
        Money $gross,
        CalculatorResult $result,
        Money $monthly,
        Money $biweekly,
        Money $weekly,
        ?Money $hourly = null,
    ): array {
        $facts = [
            ['label' => 'Annual gross', 'value' => $gross->format()],
            ['label' => 'Annual net', 'value' => $result->metrics['net_annual']->format()],
            ['label' => 'Monthly net', 'value' => $monthly->format()],
            ['label' => 'Biweekly net', 'value' => $biweekly->format()],
            ['label' => 'Weekly net', 'value' => $weekly->format()],
            ['label' => 'Federal tax', 'value' => $result->metrics['federal_tax']->format()],
            ['label' => $province->name().' tax', 'value' => $result->metrics['provincial_tax']->format()],
            ['label' => $province->usesQpp() ? 'QPP / QPP2' : 'CPP / CPP2', 'value' => $result->metrics['cpp']->add($result->metrics['cpp2'])->format()],
            ['label' => $province->usesQpp() ? 'EI + QPIP' : 'EI', 'value' => $result->metrics['ei']->add($result->metrics['qpip'])->format()],
        ];

        if ($hourly) {
            $facts[] = ['label' => 'Hourly equivalent (gross)', 'value' => $hourly->format()];
        }

        return $facts;
    }

    /**
     * @return list<array{title: string, url: string}>
     */
    private function taxSources(?Province $province = null): array
    {
        $sources = $this->freshness->sources();
        $preferred = $province?->usesQpp()
            ? ['revenu', 'quebec', 'cra', 't4127']
            : ['cra', 't4127', 'canada revenue'];

        $ranked = collect($sources)->sortBy(function (array $source) use ($preferred) {
            $haystack = strtolower($source['title'].' '.($source['url'] ?? ''));
            foreach ($preferred as $index => $needle) {
                if (str_contains($haystack, $needle)) {
                    return $index;
                }
            }

            return 99;
        })->take(2)->values()->all();

        return array_map(fn (array $source) => [
            'title' => $source['title'],
            'url' => $source['url'],
        ], $ranked);
    }

    private function formatDate(?string $date): ?string
    {
        if (! filled($date)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($date)->format('F j, Y');
        } catch (Throwable) {
            return $date;
        }
    }
}
