<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">${{ number_format($salary) }} salary after tax in {{ $province->name() }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">
            Estimated biweekly take-home for a ${{ number_format($salary) }} salary in {{ $province->name() }} is
            <strong class="text-ink">{{ $result->headlineAmount->format() }}</strong>
            in tax year {{ $taxYear }}.
        </p>

        <div class="mt-8 grid gap-8 lg:grid-cols-2">
            <div class="rounded-2xl border border-line bg-card p-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-ink-soft">{{ $result->summary }}</h2>
                <p class="mt-3 font-serif text-5xl text-ink">{{ $result->headlineAmount->format() }}</p>
                <p class="mt-2 text-ink-soft">{{ $result->headlinePeriod }}</p>
                <dl class="mt-6 space-y-2">
                    @foreach ($result->periodBreakdown as $line)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-soft">{{ $line['label'] }}</dt>
                            <dd class="font-semibold">{{ $line['amount']->format() }}</dd>
                        </div>
                    @endforeach
                </dl>
                <dl class="mt-8 space-y-2 border-t border-line pt-6">
                    @foreach ($result->annualBreakdown as $line)
                        <div class="flex justify-between gap-4">
                            <dt class="text-ink-soft">{{ $line['label'] }}</dt>
                            <dd class="font-semibold">{{ $line['amount']->format() }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-6 text-sm text-ink-soft">{{ __('calculator.disclaimer') }}</p>
            </div>

            <livewire:paycheck-calculator :province="$province->value" :salary="$salary" :auto-calculate="true" />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">{{ $province->name() }} tax context</h2>
        <p class="mt-4 text-ink-soft">{{ $content['intro'] }}</p>
        @foreach (array_slice($content['body'], 0, 2) as $section)
            <h3 class="mt-8 text-xl font-semibold">{{ $section['heading'] }}</h3>
            <p class="mt-3 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Nearby salaries in {{ $province->name() }}</h2>
        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($popularSalaries as $amount)
                @if ($amount !== $salary)
                    <a href="{{ route('paycheck.salary', [$province->slug(), $amount]) }}" class="rounded-full border border-line bg-card px-4 py-2 font-semibold hover:border-accent">
                        ${{ number_format($amount) }}
                    </a>
                @endif
            @endforeach
        </div>
    </section>

    <x-ad-slot name="content-mid" />
    <x-faq :faqs="$faqs" />
</x-layouts.app>
