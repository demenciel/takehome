<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        <x-answer-summary :summary="$summary" />
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <div class="mt-8 max-w-2xl">
            <livewire:hourly-salary-converter :mode="$page['mode']" />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @foreach ($page['sections'] as $section)
            <h2 class="mt-10 font-serif text-3xl first:mt-0">{{ $section['heading'] }}</h2>
            <p class="mt-4 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
        <p class="mt-8 text-ink-soft">
            After you convert, estimate deductions with the
            <a href="{{ route('paycheck.canada') }}" class="font-semibold text-accent-dark underline">Canada paycheck calculator</a>.
        </p>
    </section>

    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
</x-layouts.app>
