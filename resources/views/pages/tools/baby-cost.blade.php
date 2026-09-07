<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        @if ($summary)
            <x-answer-summary :summary="$summary" />
        @endif
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <p class="mt-2 text-sm text-ink-soft">Planning defaults last reviewed {{ $reviewed }}.</p>
        <x-ad-slot placement="top" />
        <div class="mt-8">
            <livewire:baby-cost-calculator :auto-calculate="true" />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @foreach ($page['sections'] as $section)
            <h2 class="mt-10 font-serif text-3xl first:mt-0">{{ $section['heading'] }}</h2>
            <p class="mt-4 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    @if ($example)
        <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">Example: editable planning defaults</h2>
            <p class="mt-4 text-ink-soft">
                Using this page’s starting defaults and no gifts, used items, childcare, or savings,
                remaining startup purchases are {{ $example['remaining_purchases']->format() }}
                and monthly baby expenses are {{ $example['monthly_recurring']->format() }}.
                That is a worksheet, not a claim that a baby costs a fixed amount in Canada.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[24rem]">
                    <caption class="mb-3 text-left text-lg font-semibold">Default planning worksheet</caption>
                    <tbody>
                        <tr>
                            <th scope="row">Planned startup</th>
                            <td>{{ $example['planned_startup']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Remaining purchases</th>
                            <td>{{ $example['remaining_purchases']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Monthly baby expenses</th>
                            <td>{{ $example['monthly_recurring']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">First-year recurring total</th>
                            <td>{{ $example['recurring_first_year']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">First-year baby cost before childcare</th>
                            <td>{{ $example['first_year_cost']->format() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Methodology</h2>
        <p class="mt-4 text-ink-soft">Startup totals add every line that is not skipped. Gifts reduce cash needed by the planned amount. Used items use the used cost when entered, and the difference from the planned amount is shown as second-hand savings. Recurring costs are monthly amounts × 12. Childcare uses the months from the start month through month 12 of the first year. CCB is included only if you type an amount. See <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">how calculations work</a>.</p>
        <p class="mt-4 text-ink-soft">
            <a href="{{ route('tools.parental') }}" class="font-semibold text-accent-dark underline">Expecting your income to change during leave?</a>
        </p>
        <p class="mt-3 text-ink-soft">
            <a href="{{ route('tools.ei_benefits') }}" class="font-semibold text-accent-dark underline">Not sure how much EI you could receive?</a>
        </p>
        <p class="mt-3 text-ink-soft">
            <a href="{{ route('paycheck.canada') }}" class="font-semibold text-accent-dark underline">Want to see your normal take-home pay before comparing leave?</a>
        </p>
        <p class="mt-4 text-ink-soft">
            Not sure about CCB?
            <a href="{{ config('external-links.government.ccb_calculator') }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">CRA Child and Family Benefits Calculator</a>
        </p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Sources</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            <li>
                <a href="{{ config('external-links.government.ccb_overview') }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">Canada Child Benefit overview</a>
                — published maximums and eligibility are set by the CRA
            </li>
            <li>
                <a href="{{ config('external-links.government.ccb_calculator') }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">CRA Child and Family Benefits Calculator</a>
                — official estimate of CCB and related benefits
            </li>
        </ul>
        <p class="mt-4 text-sm text-ink-soft">{{ config('baby-budget.disclaimer') }} Government benefit amounts and eligibility can change. Verify your entitlement with the Government of Canada. Paycheque.app is not affiliated with the Government of Canada, CRA, or Etsy.</p>
    </section>

    <x-ad-slot placement="middle" />
    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
