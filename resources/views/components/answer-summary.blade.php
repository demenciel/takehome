@props([
    'summary' => null,
    'question' => null,
    'answer' => null,
    'facts' => [],
    'jurisdiction' => null,
    'year' => null,
    'updated' => null,
    'methodologyUrl' => null,
    'sources' => [],
    'factsAs' => 'dl',
])

@php
    $question = $summary['question'] ?? $question;
    $answer = $summary['answer'] ?? $answer;
    $facts = $summary['facts'] ?? $facts;
    $jurisdiction = $summary['jurisdiction'] ?? $jurisdiction;
    $year = $summary['year'] ?? $year;
    $updated = $summary['updated'] ?? $updated;
    $methodologyUrl = $summary['methodology_url'] ?? $methodologyUrl;
    $sources = $summary['sources'] ?? $sources;
@endphp

<section {{ $attributes->class('mt-6 rounded-2xl border border-line bg-card p-5 sm:p-6') }} aria-labelledby="direct-answer-heading">
    <h2 id="direct-answer-heading" class="text-sm font-semibold uppercase tracking-wide text-ink-soft">{{ $question }}</h2>
    <p class="mt-3 text-lg leading-7 text-ink">{{ $answer }}</p>

    @if ($facts !== [])
        @if ($factsAs === 'table')
            <div class="mt-5 overflow-x-auto">
                <table class="data-table min-w-[22rem]">
                    <caption class="sr-only">Key facts</caption>
                    <tbody>
                        @foreach ($facts as $fact)
                            <tr>
                                <th scope="row">{{ $fact['label'] }}</th>
                                <td class="tabular-nums">{{ $fact['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <dl class="mt-5 grid gap-x-8 gap-y-2 sm:grid-cols-2">
                @foreach ($facts as $fact)
                    <div class="flex justify-between gap-4 border-b border-line/70 py-2">
                        <dt class="text-sm text-ink-soft">{{ $fact['label'] }}</dt>
                        <dd class="text-sm font-semibold tabular-nums">{{ $fact['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    @endif

    <p class="mt-4 text-sm text-ink-soft">
        @if ($year)
            <span>Tax year: {{ $year }}</span>
        @endif
        @if ($jurisdiction)
            <span aria-hidden="true"> · </span>
            <span>{{ $jurisdiction }}</span>
        @endif
        @if ($updated)
            <span aria-hidden="true"> · </span>
            <span>Last updated: {{ $updated }}</span>
        @endif
        @if ($methodologyUrl)
            <span aria-hidden="true"> · </span>
            <a href="{{ $methodologyUrl }}" class="font-semibold text-accent-dark underline">Methodology</a>
        @endif
    </p>

    @if ($sources !== [])
        <p class="mt-2 text-sm text-ink-soft">
            Source:
            @foreach ($sources as $source)
                <a href="{{ $source['url'] }}" class="font-semibold text-accent-dark underline" rel="noopener noreferrer">{{ $source['title'] }}</a>@if (! $loop->last); @endif
            @endforeach
        </p>
    @endif
</section>
