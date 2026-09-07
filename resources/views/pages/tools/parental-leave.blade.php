<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        @if ($summary)
            <x-answer-summary :summary="$summary" />
        @endif
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <p class="mt-2 text-sm text-ink-soft">{{ config('benefits.edition') }}. Last reviewed {{ $example['reviewed'] }}.</p>
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            <livewire:parental-leave-calculator :auto-calculate="true" />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @foreach ($page['sections'] as $section)
            <h2 class="mt-10 font-serif text-3xl first:mt-0">{{ $section['heading'] }}</h2>
            <p class="mt-4 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    @if ($example['supported'] ?? false)
        <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">Example: $80,000 in Ontario, maternity + standard parental</h2>
            <p class="mt-4 text-ink-soft">
                On an $80,000 salary, estimated federal EI is {{ $example['weekly_ei']->format() }} a week
                (the {{ $example['year'] }} weekly maximum) for {{ $example['leave_weeks'] }} counted weeks,
                or about {{ $example['ei_total']->format() }} before tax.
                Estimated regular monthly take-home while working is {{ $example['employment']['net_monthly']->format() }}.
                Eligibility still depends on insurable hours and a Service Canada claim.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[24rem]">
                    <caption class="mb-3 text-left text-lg font-semibold">Leave income example</caption>
                    <tbody>
                        <tr>
                            <th scope="row">Estimated weekly EI</th>
                            <td>{{ $example['weekly_ei']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Approximate monthly EI</th>
                            <td>{{ $example['monthly_ei']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Counted weeks</th>
                            <td>{{ $example['leave_weeks'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated total EI</th>
                            <td>{{ $example['ei_total']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Regular monthly take-home</th>
                            <td>{{ $example['employment']['net_monthly']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Monthly income during leave</th>
                            <td>{{ $example['leave_monthly']->format() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Methodology</h2>
        <p class="mt-4 text-ink-soft">Weekly EI is 55% or 33% of average insurable weekly earnings, capped at the published Service Canada maximum. Insurable earnings use the lesser of annual salary ÷ 52 and the yearly maximum insurable earnings ÷ 52. Regular take-home uses the same payroll engine as the <a href="{{ route('paycheck.canada') }}" class="font-semibold text-accent-dark underline">Canadian paycheque calculator</a>. See <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">how calculations work</a>.</p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Sources</h2>
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
        <p class="mt-4 text-sm text-ink-soft">{{ config('benefits.disclaimer') }} Government benefit amounts and eligibility can change. Verify your entitlement with the Government of Canada. Paycheque.app is not affiliated with the Government of Canada, Service Canada, CRA, or Etsy.</p>
    </section>

    <x-ad-slot placement="middle" />
    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
