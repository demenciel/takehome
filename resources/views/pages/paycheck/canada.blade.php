<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">Canada Paycheck Calculator</h1>
        <x-answer-summary :summary="$summary" />
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">Estimate take-home pay anywhere in Canada. Choose your province — the calculator never assumes it for you.</p>
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            <livewire:paycheck-calculator />
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <h2 class="font-serif text-3xl">Salary after tax in Canada</h2>
        <p class="mt-4 max-w-3xl text-ink-soft">Canadian payroll deductions usually include federal income tax, provincial or territorial income tax, CPP or QPP, and EI. Quebec employees also pay QPIP and receive a federal tax abatement. This national calculator uses {{ $taxFreshness->year() }} CRA T4127 Option 1 for a full-year employee claiming the basic personal amount.</p>
    </section>

    <section id="provinces" class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Province paycheck calculators</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($provinces as $province)
                <li>
                    <a href="{{ route('paycheck.province', $province->slug()) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        {{ $province->name() }} paycheck calculator
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
