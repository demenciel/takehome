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

        <h2 class="mt-10 font-serif text-3xl">When tax data is updated</h2>
        <p class="mt-4 text-ink-soft">Federal and most provincial amounts change in January. CRA sometimes publishes a July T4127 for mid-year provincial changes. This site records the retrieved date with the rule set. The {{ $taxFreshness->year() }} files were last checked on {{ $taxFreshness->lastUpdatedLabel() }}.</p>
        <p class="mt-4"><a href="{{ route('tax-rates') }}" class="font-semibold text-accent-dark underline">Current rates used by the calculator</a></p>
    </article>
</x-layouts.app>
