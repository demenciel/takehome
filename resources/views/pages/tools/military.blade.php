<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        <x-answer-summary :summary="$summary" />
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <p class="mt-2 text-sm text-ink-soft">{{ $sources['edition'] }}. Pay tables last checked {{ $sources['retrieved_date'] }}.</p>
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            <livewire:military-salary-calculator
                :rank="$example['pay']['rank']"
                :increment="$example['pay']['increment']"
                frequency="monthly"
                :province="$example['payroll']->inputs['province']"
                :auto-calculate="true"
            />
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
            <h2 class="font-serif text-3xl">Example: Regular Force Corporal</h2>
            <p class="mt-4 text-ink-soft">
                Official base pay comes from the published DND table ({{ $example['edition'] }}).
                Estimated take-home uses {{ $example['payroll']->inputs['province_name'] }} payroll rules and excludes allowances unless entered.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[24rem]">
                    <caption class="mb-3 text-left text-lg font-semibold">Published pay and estimated take-home</caption>
                    <tbody>
                        <tr>
                            <th scope="row">Component</th>
                            <td>{{ $example['component_label'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Rank</th>
                            <td>{{ $example['pay']['rank_name'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Pay increment</th>
                            <td>{{ $example['pay']['increment_label'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Official monthly base pay</th>
                            <td>{{ $example['pay']['rate']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Pay-table effective date</th>
                            <td>{{ $example['edition'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Annualized base pay</th>
                            <td>{{ $example['base_annual']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated annual take-home</th>
                            <td>{{ $example['payroll']->metrics['net_annual']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Pension included</th>
                            <td>{{ $example['pension']['included'] ? 'Yes (Regular Force estimate)' : 'No' }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Allowances included</th>
                            <td>No, unless entered</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Official pay-data source</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            @foreach ($sources['sources'] as $source)
                <li>
                    <a href="{{ $source['url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">{{ $source['title'] }}</a>
                    — {{ $source['publisher'] }}. {{ $source['used_for'] }}.
                </li>
            @endforeach
        </ul>
        <p class="mt-4 text-sm text-ink-soft">{{ $sources['disclaimer'] }}</p>
    </section>

    <x-ad-slot placement="middle" />
    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
