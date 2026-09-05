<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <h1 class="font-serif text-4xl">About {{ config('app.name') }}</h1>
        <p class="mt-6 text-lg text-ink-soft">{{ config('app.name') }} is an independent Canadian take-home pay calculator. It estimates federal tax, provincial or territorial tax, CPP or QPP, EI, and net pay from the salary, province, and pay frequency you enter.</p>
        <h2 class="mt-10 font-serif text-3xl">Not a government service</h2>
        <p class="mt-4 text-ink-soft">This site is not affiliated with the Canada Revenue Agency, Revenu Québec, or any provincial government. Official withholding tools and employer payroll software remain the authority for an actual paycheque.</p>
        <h2 class="mt-10 font-serif text-3xl">Where the numbers come from</h2>
        <p class="mt-4 text-ink-soft">Tax rules are stored as versioned data files and calculated in PHP. There is no AI API and no call to an external tax service when you calculate. The current rules follow CRA T4127 payroll formulas, with Revenu Québec parameters for Quebec provincial tax. Details are on the <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">methodology</a> page.</p>
        <h2 class="mt-10 font-serif text-3xl">What this is not</h2>
        <p class="mt-4 text-ink-soft">It is not an employer payroll system, a tax-filing product, or official tax advice. Actual paycheques depend on TD1 claims, benefits, pensions, and your employer’s software.</p>
        <p class="mt-6"><a href="{{ route('privacy') }}" class="font-semibold text-accent-dark underline">Privacy</a> — calculations do not require an account.</p>
    </article>
</x-layouts.app>
