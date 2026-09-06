<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <x-tax-freshness class="mt-4" />
        <p class="mt-2 text-sm text-ink-soft">{{ $sources['edition'] }}. Pay tables last checked {{ $sources['retrieved_date'] }}.</p>
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            <livewire:military-salary-calculator />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @foreach ($page['sections'] as $section)
            <h2 class="mt-10 font-serif text-3xl first:mt-0">{{ $section['heading'] }}</h2>
            <p class="mt-4 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Official pay-data source</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            @foreach ($sources['sources'] as $source)
                <li>
                    <a href="{{ $source['url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">{{ $source['title'] }}</a>
                    — {{ $source['publisher'] }}. {{ $source['used_for'] }}.
                </li>
            @endforeach
        </ul>
        <p class="mt-4 text-sm text-ink-soft">{{ $sources['disclaimer'] }}</p>
    </section>

    <x-ad-slot placement="middle" />
    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
