<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">${{ number_format($salary) }} Salary After Tax in {{ $province->name() }}</h1>
        <x-answer-summary :summary="$summary" facts-as="table" />

        <div class="mt-8 rounded-2xl border border-line bg-card p-6 sm:p-8">
            <p class="text-sm font-semibold uppercase tracking-wide text-ink-soft">Estimated annual take-home</p>
            <p class="mt-3 font-serif text-5xl text-ink">{{ $result->metrics['net_annual']->format() }}</p>
            <p class="mt-2 text-ink-soft">on a ${{ number_format($salary) }} salary in {{ $province->name() }}</p>
        </div>

        <div class="mt-8 overflow-x-auto">
            <table class="data-table min-w-[24rem]">
                <caption class="mb-3 text-left text-lg font-semibold">Pay frequency estimates</caption>
                <thead>
                    <tr>
                        <th scope="col">Pay frequency</th>
                        <th scope="col">Estimated take-home</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($frequencies as $row)
                        <tr>
                            <th scope="row">{{ $row['frequency']->label() }}</th>
                            <td>{{ $row['net']->format() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-sm text-ink-soft">{{ __('calculator.disclaimer') }}</p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">How Much Is ${{ number_format($salary) }} After Tax in {{ $province->name() }}?</h2>
        <p class="mt-4 text-ink-soft">{{ $explanation }}</p>
        <p class="mt-4 text-ink-soft">{{ $content['intro'] }}</p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">What is ${{ number_format($salary) }} per month after tax?</h2>
        <p class="mt-4 text-ink-soft">{{ $faqs[1]['answer'] }}</p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">What is ${{ number_format($salary) }} biweekly after tax?</h2>
        <p class="mt-4 text-ink-soft">{{ $faqs[2]['answer'] }}</p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">What deductions come off this salary?</h2>
        <p class="mt-4 text-ink-soft">{{ $faqs[3]['answer'] }} {{ $faqs[4]['answer'] }}</p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">What Is ${{ number_format($salary) }} Per Hour?</h2>
        <p class="mt-4 text-ink-soft">
            Using {{ $hourAssumption }}, ${{ number_format($salary) }} is about
            <strong class="text-ink">{{ $hourly->format() }}/hour</strong> before tax.
            That is a conversion, not a contracted wage.
        </p>
        <p class="mt-3">
            <a href="{{ route('tools.salary_to_hourly') }}" class="font-semibold text-accent-dark underline">Salary-to-hourly calculator</a>
            if you want to change the hours assumption.
        </p>
    </section>

    <x-ad-slot placement="middle" />

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Compare Salaries</h2>
        <p class="mt-3 text-ink-soft">
            @if ($neighbors['previous'])
                <a href="{{ route('paycheck.salary', [$province->slug(), $neighbors['previous']]) }}" class="font-semibold text-accent-dark underline">${{ number_format($neighbors['previous']) }}</a>
                ←
            @endif
            ${{ number_format($salary) }}
            @if ($neighbors['next'])
                →
                <a href="{{ route('paycheck.salary', [$province->slug(), $neighbors['next']]) }}" class="font-semibold text-accent-dark underline">${{ number_format($neighbors['next']) }}</a>
            @endif
            in {{ $province->name() }}
        </p>
        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($compare as $amount)
                @if ($amount === $salary)
                    <span class="rounded-full border border-accent bg-accent/10 px-4 py-2 font-semibold">${{ number_format($amount) }}</span>
                @else
                    <a href="{{ route('paycheck.salary', [$province->slug(), $amount]) }}" class="rounded-full border border-line bg-card px-4 py-2 font-semibold hover:border-accent">
                        ${{ number_format($amount) }}
                    </a>
                @endif
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Calculate Your Own Paycheque</h2>
        <p class="mt-3 max-w-2xl text-ink-soft">Change the salary, hours, or optional deductions. Calculator state stays on this page and is not a separate indexable URL.</p>
        <div class="mt-6 max-w-2xl">
            <livewire:paycheck-calculator :province="$province->value" :salary="$salary" :auto-calculate="true" />
        </div>
    </section>

    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
