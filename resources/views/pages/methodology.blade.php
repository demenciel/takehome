<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">How paycheck calculations work</h1>
        <x-tax-freshness class="mt-4" />
        <p class="mt-6 text-lg text-ink-soft">{{ config('app.name') }} estimates Canadian employment take-home pay from a salary, a province or territory, and a pay frequency. It is a calculator, not an employer payroll system and not a CRA or Revenu Québec service.</p>

        <h2 class="mt-10 font-serif text-3xl">The tax rules that are used</h2>
        <p class="mt-4 text-ink-soft">Rates live in versioned files under <code>resources/tax/{{ $taxFreshness->year() }}/</code>. The calculation engine reads those files. Updating 2027 should mean adding a new folder and switching <code>CURRENT_TAX_YEAR</code>, not rewriting the calculator.</p>
        @if ($edition)
            <p class="mt-4 text-ink-soft">Current edition: {{ $edition }}.</p>
        @endif

        <h2 class="mt-10 font-serif text-3xl">Federal and provincial income tax</h2>
        <p class="mt-4 text-ink-soft">The engine follows CRA T4127 Option 1 for a full-year employee with constant pay and claim code 1 (basic personal amount only). Federal tax uses the published brackets, Canada Employment Amount, and the BPAF phase-out for higher incomes. Provincial or territorial tax uses that jurisdiction’s brackets and credits, including Ontario’s health premium and surtax, the BC tax reduction, Alberta’s K5P credit, and Yukon’s K4P credit where those rules apply.</p>

        <h2 class="mt-10 font-serif text-3xl">Quebec is not treated as a regular province</h2>
        <p class="mt-4 text-ink-soft">CRA does not calculate Quebec provincial tax in T4127 (factor T2 is zero). Quebec income tax uses Revenu Québec brackets and the provincial basic personal amount. Quebec employees contribute to QPP instead of CPP, pay QPIP, pay a lower EI rate, and receive a 16.5% federal abatement.</p>

        <h2 class="mt-10 font-serif text-3xl">CPP and QPP</h2>
        <p class="mt-4 text-ink-soft">Pension contributions are calculated on annual pensionable earnings with the basic exemption, YMPE/MPE, and YAMPE. Additional CPP/QPP and CPP2/QPP2 reduce taxable income (CRA factor F5). Once the annual maximum is reached, further contributions stop.</p>

        <h2 class="mt-10 font-serif text-3xl">Employment Insurance</h2>
        <p class="mt-4 text-ink-soft">EI uses the annual maximum insurable earnings and the employee rate for the employee’s province. Quebec uses the reduced EI rate because QPIP exists separately.</p>

        <h2 class="mt-10 font-serif text-3xl">Pay frequency</h2>
        <p class="mt-4 text-ink-soft">Annual tax and contribution totals are estimated first. Monthly divides by 12, semimonthly by 24, biweekly by 26, and weekly by 52. The annual totals stay the same; only the per-cheque display changes. A live payroll system may allocate the CPP exemption per period and stop CPP or EI mid-year, so an individual cheque can differ by a few dollars.</p>

        <h2 class="mt-10 font-serif text-3xl">Why an employer paycheque can differ</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            <li>Different TD1 / provincial claim codes, extra amounts, or multiple jobs</li>
            <li>Taxable benefits, allowances, or commissions</li>
            <li>Employer pension, union dues, or additional tax requested</li>
            <li>Hired part-way through the year</li>
            <li>Prescribed-zone / northern residents amounts</li>
            <li>Employer software rounding or a different T4127 option</li>
        </ul>

        <h2 class="mt-10 font-serif text-3xl">Where the data comes from</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            @foreach ($sources as $source)
                <li>
                    <a href="{{ $source['url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">{{ $source['title'] }}</a>
                    @if (! empty($source['used_for']))
                        — {{ $source['used_for'] }}
                    @endif
                </li>
            @endforeach
        </ul>

        <h2 class="mt-10 font-serif text-3xl">Overtime pay calculator</h2>
        <p class="mt-4 text-ink-soft">Gross overtime uses jurisdiction rules stored in <code>resources/employment/overtime.php</code>: the standard weekly threshold, a daily threshold where the statute has one, and the overtime multiplier. Hours you enter as overtime are paid at that premium. Regular hours stay at the regular wage.</p>
        <p class="mt-4 text-ink-soft">After-tax overtime is not overtime pay times an assumed tax rate. The calculator annualizes the pay period (weekly × 52, biweekly × 26, and so on), then compares the payroll engine with and without the overtime. The difference is the estimated amount you keep. Occupations, averaging agreements, and federally regulated workplaces can follow different employment-standards rules.</p>

        <h2 class="mt-10 font-serif text-3xl">Bonus tax calculator</h2>
        <p class="mt-4 text-ink-soft">A bonus is treated as extra employment income. The engine runs twice — on salary alone, then on salary plus bonus. Net bonus is the difference in annual take-home. CPP/QPP, CPP2/QPP2, EI, and QPIP only increase if you are still below those annual ceilings. Employer withholding on the bonus cheque can differ from this annual estimate.</p>

        <h2 class="mt-10 font-serif text-3xl">Raise calculator</h2>
        <p class="mt-4 text-ink-soft">The raise calculator compares payroll results on the current salary and the new salary. Net raise is new take-home minus old take-home. Monthly, biweekly, and weekly extras divide that annual difference by 12, 26, and 52. You can also enter a raise percentage; the new salary is calculated first, then the same comparison runs.</p>

        <h2 class="mt-10 font-serif text-3xl">Canadian Armed Forces salary calculator</h2>
        <p class="mt-4 text-ink-soft">CAF base pay is read from versioned files under <code>resources/military/2025/</code>. The official National Defence Regular Force monthly tables and Reserve Force Class A/B daily tables are still published as effective 1 April 2025. Those are the current published rank scales used here. April 2026 CAF compensation changes were mainly allowances (environmental, domestic operations, and similar), which this calculator does not estimate.</p>
        <p class="mt-4 text-ink-soft">Take-home pay uses the same {{ $taxFreshness->year() }} federal/provincial tax, CPP/QPP, CPP2/QPP2, EI, and QPIP engine as the paycheck calculator. Regular Force pension is estimated from Treasury Board rates effective 1 January 2026: 9.10% up to the YMPE of $74,600 and 11.69% above it. Members with 35 years of pensionable service are not modeled. Reserve Force pension is omitted because 2026 Reserve plan rates are not published on that same table.</p>
        <p class="mt-4 text-ink-soft">Actual CAF pay can differ because of occupation group, specialist pay, Class of reserve service, allowances, and individual pensionable service. Allowances and benefits are not included unless the user types an amount.</p>

        <h2 class="mt-10 font-serif text-3xl">Parental leave calculator</h2>
        <p class="mt-4 text-ink-soft">Federal EI maternity and parental rates live in <code>config/benefits.php</code>, versioned by year. Weekly insurable earnings are the lesser of annual salary ÷ 52 and the yearly maximum insurable earnings ÷ 52. The weekly benefit is the program rate (55% or 33%) times that amount, then capped at the published Service Canada maximum. When earnings sit at the yearly maximum, the published weekly maximum is used rather than a rounded 55% or 33% of the maximum weekly insurable amount.</p>
        <p class="mt-4 text-ink-soft">Maternity plus parental leave splits planned weeks into up to 15 maternity weeks, then parental weeks, each capped at the individual and shared maxima. Regular take-home uses the same {{ $taxFreshness->year() }} payroll engine as the paycheck calculator. Employer top-ups default to “top income up to X% of regular gross weekly salary.” Québec is not estimated: maternity and parental benefits there are paid through QPIP.</p>

        <h2 class="mt-10 font-serif text-3xl">EI maternity and parental benefits</h2>
        <p class="mt-4 text-ink-soft">The EI benefits page uses the same <code>ParentalBenefitsService</code>. It estimates one program at a time — maternity, standard parental, or extended parental — and applies week caps and shared-parent limits. It does not confirm eligibility or withhold tax on the benefit.</p>

        <h2 class="mt-10 font-serif text-3xl">Baby cost calculator</h2>
        <p class="mt-4 text-ink-soft">Startup and monthly defaults live in <code>config/baby-budget.php</code>. They are planning estimates, not a claim that a baby costs a fixed amount. Gifts remove the planned amount from remaining purchases. Used items use the used cost when entered. Childcare is included only when the user enters a monthly amount and start month; there is no Canada-wide $10/day assumption. Canada Child Benefit is included only if the user types an amount. This site does not implement the CRA CCB formula.</p>

        <h2 class="mt-10 font-serif text-3xl">When tax data is updated</h2>
        <p class="mt-4 text-ink-soft">Federal and most provincial amounts change in January. CRA sometimes publishes a July T4127 for mid-year provincial changes. This site records the retrieved date with the rule set. The {{ $taxFreshness->year() }} files were last checked on {{ $taxFreshness->lastUpdatedLabel() }}.</p>
        <p class="mt-4"><a href="{{ route('tax-rates') }}" class="font-semibold text-accent-dark underline">Current rates used by the calculator</a></p>
    </article>
</x-layouts.app>
