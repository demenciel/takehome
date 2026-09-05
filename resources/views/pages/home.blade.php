<x-layouts.app :seo="$seo">
    <section class="mx-auto max-w-6xl px-4 pb-8 pt-10 sm:px-6 sm:pt-16">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-accent">{{ config('app.name') }} · {{ $taxYear }}</p>
            <h1 class="mt-4 font-serif text-4xl leading-tight text-ink sm:text-5xl">
                {{ __('calculator.hero_title') }}
            </h1>
            <p class="mt-5 max-w-2xl text-lg text-ink-soft">{{ __('calculator.hero_subtitle') }}</p>
        </div>

        <div class="mt-10 grid items-start gap-10 lg:grid-cols-[minmax(0,1.1fr)_minmax(16rem,0.7fr)]">
            <livewire:paycheck-calculator />

            <aside class="hidden rounded-2xl border border-line bg-card p-6 lg:block">
                <h2 class="font-serif text-2xl">A trustworthy estimate</h2>
                <p class="mt-3 text-ink-soft">Built on CRA payroll formulas. No account. No email. Your salary is not stored.</p>
                <x-ad-slot name="sidebar" class="mt-6" />
            </aside>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6" aria-labelledby="affects-heading">
        <h2 id="affects-heading" class="font-serif text-3xl">What affects your paycheck?</h2>
        <div class="mt-8 grid gap-6 md:grid-cols-2">
            <article class="rounded-2xl bg-card p-6">
                <h3 class="text-xl font-semibold">Federal vs provincial tax</h3>
                <p class="mt-3 text-ink-soft">Every employee pays federal income tax. Your province or territory adds its own brackets, credits, and in some cases a surtax or health premium. Quebec residents also receive a federal abatement.</p>
            </article>
            <article class="rounded-2xl bg-card p-6">
                <h3 class="text-xl font-semibold">CPP and EI explained</h3>
                <p class="mt-3 text-ink-soft">CPP (or QPP in Quebec) and EI are deducted from employment income up to annual maximums. They are not income tax, but they do reduce take-home pay and generate federal and provincial tax credits.</p>
            </article>
            <article class="rounded-2xl bg-card p-6">
                <h3 class="text-xl font-semibold">Gross vs net income</h3>
                <p class="mt-3 text-ink-soft">Gross is the salary on your offer letter. Net is what lands in your account after tax, CPP or QPP, EI, and any payroll deductions you or your employer add.</p>
            </article>
            <article class="rounded-2xl bg-card p-6">
                <h3 class="text-xl font-semibold">Marginal vs effective tax rate</h3>
                <p class="mt-3 text-ink-soft">Your marginal rate is the rate on the next dollar. The effective rate shown in the results is estimated income tax divided by gross salary — usually the more useful number for comparing offers.</p>
            </article>
        </div>
    </section>

    <x-ad-slot name="content-mid" />

    <section id="provinces" class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        <h2 class="font-serif text-3xl">Choose your province</h2>
        <p class="mt-3 max-w-2xl text-ink-soft">Each page includes the same calculator with province-specific tax notes. We never assume your province on the homepage.</p>
        <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($provinces as $province)
                <li>
                    <a href="{{ route('paycheck.province', $province->slug()) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        {{ $province->name() }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        <h2 class="font-serif text-3xl">Popular salaries</h2>
        <p class="mt-3 max-w-2xl text-ink-soft">Open a salary page for a province when you want a ready-made estimate. These are the amounts we index.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($popularSalaries as $amount)
                <a href="{{ route('paycheck.salary', ['ontario', $amount]) }}" class="rounded-full border border-line bg-card px-4 py-2 font-semibold hover:border-accent">
                    ${{ number_format($amount) }}
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
        <h2 class="font-serif text-3xl">Biweekly vs monthly pay</h2>
        <p class="mt-4 text-ink-soft">Biweekly pay splits the same annual estimate across 26 periods. Monthly uses 12. The annual tax, CPP, and EI totals stay the same in this calculator; only the per-cheque amount changes.</p>
    </section>

    <x-faq :faqs="$faqs" />
    <x-ad-slot name="bottom-banner" />
</x-layouts.app>
