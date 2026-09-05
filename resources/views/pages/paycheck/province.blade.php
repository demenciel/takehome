<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $province->name() }} Paycheck Calculator</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $content['intro'] }}</p>
        <x-tax-freshness class="mt-4" />
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            <livewire:paycheck-calculator :province="$province->value" />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">{{ $province->name() }} Paycheck Calculator {{ $taxFreshness->year() }}</h2>
        <p class="mt-4 text-ink-soft">{{ $content['intro'] }} This page uses the {{ $taxFreshness->year() }} payroll rules stored for {{ $province->name() }}. Results are estimates for a full-year employee claiming the basic personal amount.</p>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">{{ $province->name() }} Take-Home Pay</h2>
        <p class="mt-3 max-w-3xl text-ink-soft">Example annual take-home estimates. These are rounded planning figures, not a pay stub.</p>
        <div class="mt-6 overflow-x-auto">
            <table class="data-table min-w-[28rem]">
                <thead>
                    <tr>
                        <th scope="col">Gross salary</th>
                        <th scope="col">Estimated take-home</th>
                        <th scope="col">Effective tax rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($examples as $example)
                        <tr>
                            <th scope="row">
                                @if ($example['url'])
                                    <a href="{{ $example['url'] }}" class="font-semibold text-accent-dark underline">
                                        ${{ number_format($example['salary']) }} in {{ $province->name() }}
                                    </a>
                                @else
                                    ${{ number_format($example['salary']) }}
                                @endif
                            </th>
                            <td>{{ $example['net_annual']->format() }}</td>
                            <td>{{ $example['effective_tax_rate'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <x-ad-slot placement="middle" />

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">How {{ $province->name() }} Income Tax Works</h2>
        @foreach ($content['body'] as $section)
            <h3 class="mt-8 text-xl font-semibold">{{ $section['heading'] }}</h3>
            <p class="mt-3 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">{{ $province->name() }} Payroll Deductions</h2>
        @foreach ($deductions as $section)
            <h3 class="mt-8 text-xl font-semibold">{{ $section['heading'] }}</h3>
            <p class="mt-3 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    @if ($indexableSalaries !== [])
        <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">{{ $province->name() }} salary pages</h2>
            <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($indexableSalaries as $amount)
                    <li>
                        <a href="{{ route('paycheck.salary', [$province->slug(), $amount]) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                            ${{ number_format($amount) }} salary in {{ $province->name() }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
