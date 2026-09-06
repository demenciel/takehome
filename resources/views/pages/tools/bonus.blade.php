<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        @if ($summary)
            <x-answer-summary :summary="$summary" />
        @endif
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            @if ($province)
                <livewire:bonus-tax-calculator :province="$province->value" :auto-calculate="true" />
            @else
                <livewire:bonus-tax-calculator :auto-calculate="true" />
            @endif
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
            <h2 class="font-serif text-3xl">Example: $80,000 salary + $10,000 bonus</h2>
            <p class="mt-4 text-ink-soft">
                On an $80,000 salary plus a $10,000 bonus
                @if ($province)
                    in {{ $province->name() }}
                @endif
                , the estimated incremental tax is {{ $example['tax_delta']->format() }}
                and the extra CPP/QPP and EI/QPIP is {{ $example['pension_delta']->add($example['insurance_delta'])->format() }}.
                Estimated net bonus: {{ $example['net_annual_delta']->format() }} ({{ $example['keep_percent'] }}% kept).
                Employer withholding may differ; this is the estimated annual tax liability.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[24rem]">
                    <caption class="mb-3 text-left text-lg font-semibold">Bonus impact</caption>
                    <tbody>
                        <tr>
                            <th scope="row">Gross bonus</th>
                            <td>$10,000.00</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated additional tax</th>
                            <td>{{ $example['tax_delta']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">CPP/QPP impact</th>
                            <td>{{ $example['pension_delta']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">EI/QPIP impact</th>
                            <td>{{ $example['insurance_delta']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated net bonus</th>
                            <td>{{ $example['net_annual_delta']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Share kept</th>
                            <td>{{ $example['keep_percent'] }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Methodology</h2>
        <p class="mt-4 text-ink-soft">Net bonus is take-home on salary plus bonus minus take-home on salary alone. See <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">how paycheck calculations work</a> for the underlying tax engine.</p>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Bonus tax by province</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($provinces as $item)
                <li>
                    <a href="{{ route('tools.bonus.province', $item->slug()) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        {{ $item->name() }} bonus tax calculator
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <x-ad-slot placement="middle" />
    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
