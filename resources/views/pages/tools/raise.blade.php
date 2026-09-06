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
                <livewire:raise-calculator :province="$province->value" :auto-calculate="true" />
            @else
                <livewire:raise-calculator :auto-calculate="true" />
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
            <h2 class="font-serif text-3xl">Example: $75,000 to $85,000</h2>
            <p class="mt-4 text-ink-soft">
                Moving from $75,000 to $85,000
                @if ($province)
                    in {{ $province->name() }}
                @endif
                is a {{ $example['gross_delta']->format() }} raise.
                Estimated extra take-home is {{ $example['net_annual_delta']->format() }} a year
                ({{ $example['net_annual_delta']->divideBy(12)->format() }} a month), or {{ $example['keep_percent'] }}% of the raise.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[28rem]">
                    <thead>
                        <tr>
                            <th scope="col">Metric</th>
                            <th scope="col">Before</th>
                            <th scope="col">After</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">Gross salary</th>
                            <td>$75,000.00</td>
                            <td>$85,000.00</td>
                        </tr>
                        <tr>
                            <th scope="row">Net annual pay</th>
                            <td>{{ $example['baseline']->metrics['net_annual']->format() }}</td>
                            <td>{{ $example['modified']->metrics['net_annual']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Income tax</th>
                            <td>{{ $example['baseline']->metrics['federal_tax']->add($example['baseline']->metrics['provincial_tax'])->format() }}</td>
                            <td>{{ $example['modified']->metrics['federal_tax']->add($example['modified']->metrics['provincial_tax'])->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Monthly net</th>
                            <td>{{ $example['baseline']->metrics['net_annual']->divideBy(12)->format() }}</td>
                            <td>{{ $example['modified']->metrics['net_annual']->divideBy(12)->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Biweekly net</th>
                            <td>{{ $example['baseline']->metrics['net_annual']->divideBy(26)->format() }}</td>
                            <td>{{ $example['modified']->metrics['net_annual']->divideBy(26)->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Net raise</th>
                            <td colspan="2">{{ $example['net_annual_delta']->format() }} a year ({{ $example['keep_percent'] }}% kept)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Raise calculator by province</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($provinces as $item)
                <li>
                    <a href="{{ route('tools.raise.province', $item->slug()) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        {{ $item->name() }} raise calculator
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
