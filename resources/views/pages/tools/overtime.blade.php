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
                <livewire:overtime-pay-calculator :province="$province->value" />
            @else
                <livewire:overtime-pay-calculator />
            @endif
        </div>
    </section>

    @if ($province && isset($page['rules']))
        @php $rules = $page['rules']; @endphp
        <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">Standard overtime in {{ $province->name() }}</h2>
            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-card p-4">
                    <dt class="text-sm text-ink-soft">Weekly threshold</dt>
                    <dd class="mt-1 text-2xl font-semibold">{{ $rules['weekly_threshold'] }} hours</dd>
                </div>
                <div class="rounded-xl bg-card p-4">
                    <dt class="text-sm text-ink-soft">Standard multiplier</dt>
                    <dd class="mt-1 text-2xl font-semibold">{{ $rules['multiplier'] }}×</dd>
                </div>
                @if ($rules['daily_threshold'])
                    <div class="rounded-xl bg-card p-4">
                        <dt class="text-sm text-ink-soft">Daily threshold</dt>
                        <dd class="mt-1 text-2xl font-semibold">{{ $rules['daily_threshold'] }} hours</dd>
                    </div>
                @endif
                @if ($rules['daily_double_threshold'])
                    <div class="rounded-xl bg-card p-4">
                        <dt class="text-sm text-ink-soft">Daily double time</dt>
                        <dd class="mt-1 text-2xl font-semibold">{{ $rules['daily_double_threshold'] }} hours at {{ $rules['double_multiplier'] }}×</dd>
                    </div>
                @endif
            </dl>
            <p class="mt-6 text-ink-soft">{{ $rules['summary'] }}</p>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @foreach ($page['sections'] as $section)
            <h2 class="mt-10 font-serif text-3xl first:mt-0">{{ $section['heading'] }}</h2>
            <p class="mt-4 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    @if ($example)
        <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">Example after-tax overtime</h2>
            <p class="mt-4 text-ink-soft">
                At $30/hour with 40 regular hours and 8 overtime hours every week
                @if ($province)
                    in {{ $province->name() }}
                @endif
                , this estimate annualizes {{ $example['annual_with']->format() }} and compares it with {{ $example['annual_without']->format() }} without the overtime.
                The overtime premium is {{ $example['overtime_pay']->format() }} gross per week; the estimated after-tax value of that overtime is {{ $example['after_tax_overtime']->format() }} per week.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[24rem]">
                    <caption class="mb-3 text-left text-lg font-semibold">Weekly overtime example</caption>
                    <tbody>
                        <tr>
                            <th scope="row">Hourly wage</th>
                            <td>$30.00</td>
                        </tr>
                        <tr>
                            <th scope="row">Overtime hours</th>
                            <td>8</td>
                        </tr>
                        <tr>
                            <th scope="row">Overtime rate</th>
                            <td>{{ $example['overtime_rate']->format() }}/hour</td>
                        </tr>
                        <tr>
                            <th scope="row">Gross overtime</th>
                            <td>{{ $example['overtime_pay']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated after-tax overtime</th>
                            <td>{{ $example['after_tax_overtime']->format() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($province && isset($page['rules']['source']))
        <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">Source</h2>
            <p class="mt-4 text-ink-soft">
                <a href="{{ $page['rules']['source']['url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">
                    {{ $page['rules']['source']['title'] }}
                </a>
                — {{ $page['rules']['source']['publisher'] }}.
                Employment-standards data last checked {{ $rulesProvider->retrievedDate() }}.
            </p>
            <p class="mt-4 text-sm text-ink-soft">{{ $rulesProvider->disclaimer() }}</p>
        </section>
    @else
        <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
            <h2 class="font-serif text-3xl">Official overtime sources</h2>
            <p class="mt-4 text-ink-soft">Each province and territory page links the employment-standards page used for that jurisdiction. Rules were last checked {{ $rulesProvider->retrievedDate() }}.</p>
            <p class="mt-4 text-sm text-ink-soft">{{ $rulesProvider->disclaimer() }}</p>
        </section>
    @endif

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Overtime by province</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($provinces as $item)
                <li>
                    <a href="{{ route('tools.overtime.province', $item->slug()) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        {{ $item->name() }} overtime calculator
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
