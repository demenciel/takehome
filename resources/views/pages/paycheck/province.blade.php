<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $province->name() }} Paycheck Calculator</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $content['intro'] }}</p>
        <div class="mt-8 max-w-2xl">
            <livewire:paycheck-calculator :province="$province->value" />
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        @foreach ($content['body'] as $section)
            <h2 class="mt-10 font-serif text-3xl first:mt-0">{{ $section['heading'] }}</h2>
            <p class="mt-4 text-ink-soft">{{ $section['copy'] }}</p>
        @endforeach
    </section>

    <x-ad-slot name="content-mid" />

    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Example salaries in {{ $province->name() }}</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($popularSalaries as $amount)
                <li>
                    <a href="{{ route('paycheck.salary', [$province->slug(), $amount]) }}" class="flex min-h-14 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        ${{ number_format($amount) }} salary in {{ $province->name() }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <x-faq :faqs="$faqs" />
</x-layouts.app>
