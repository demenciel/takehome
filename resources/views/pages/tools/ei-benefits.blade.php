<x-layouts.app :seo="$seo">
    <x-breadcrumbs :items="$seo->breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pb-8 pt-8 sm:px-6">
        <h1 class="font-serif text-4xl text-ink sm:text-5xl">{{ $page['h1'] }}</h1>
        @if ($summary)
            <x-answer-summary :summary="$summary" />
        @endif
        <p class="mt-4 max-w-2xl text-lg text-ink-soft">{{ $page['intro'] }}</p>
        <p class="mt-2 text-sm text-ink-soft">{{ $page['verified_label'] }}. Last reviewed {{ $reviewed }}.</p>
        <x-ad-slot placement="top" />
        <div class="mt-8 max-w-2xl">
            <livewire:ei-maternity-parental-calculator :auto-calculate="true" />
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
            <h2 class="font-serif text-3xl">Example: $80,000 maternity benefits</h2>
            <p class="mt-4 text-ink-soft">
                On $80,000 of annual earnings, estimated maternity EI is {{ $example['weekly_benefit']->format() }} a week
                for {{ $example['weeks'] }} weeks, or {{ $example['total']->format() }} before tax.
                That uses the {{ $example['rate_percent'] }}% rate and the published weekly maximum of {{ $example['max_weekly']->format() }}.
            </p>
            <div class="mt-6 overflow-x-auto">
                <table class="data-table min-w-[24rem]">
                    <caption class="mb-3 text-left text-lg font-semibold">Maternity EI example</caption>
                    <tbody>
                        <tr>
                            <th scope="row">Benefit rate</th>
                            <td>{{ $example['rate_percent'] }}%</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated weekly benefit</th>
                            <td>{{ $example['weekly_benefit']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Applicable maximum</th>
                            <td>{{ $example['max_weekly']->format() }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Weeks</th>
                            <td>{{ $example['weeks'] }} / {{ $example['max_weeks'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Estimated total</th>
                            <td>{{ $example['total']->format() }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Methodology</h2>
        <p class="mt-4 text-ink-soft">The weekly estimate is the benefit rate times average insurable weekly earnings, capped at the published maximum for that program. If you enter weekly insurable earnings, they are annualized as weekly × 52 before the same cap is applied. Shared-parent entries reduce weeks when the combined total exceeds the shared maximum. See <a href="{{ route('methodology') }}" class="font-semibold text-accent-dark underline">how calculations work</a>.</p>
        <p class="mt-4 text-ink-soft">
            <a href="{{ route('tools.parental') }}" class="font-semibold text-accent-dark underline">Want to see what this means for your actual household budget?</a>
        </p>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <h2 class="font-serif text-3xl">Sources</h2>
        <ul class="mt-4 list-disc space-y-2 pl-6 text-ink-soft">
            @foreach ($sources as $source)
                <li>
                    <a href="{{ $source['url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">{{ $source['title'] }}</a>
                    @if (! empty($source['used_for']))
                        — {{ $source['used_for'] }}
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="mt-4 text-sm text-ink-soft">{{ config('benefits.disclaimer') }} Government benefit amounts and eligibility can change. Verify your entitlement with the Government of Canada. Paycheque.app is not affiliated with the Government of Canada, Service Canada, CRA, or Etsy.</p>
    </section>

    <x-ad-slot placement="middle" />
    <x-faq :faqs="$faqs" />
    <x-related-calculators :links="$related" />
    <x-ad-slot placement="bottom" />
</x-layouts.app>
