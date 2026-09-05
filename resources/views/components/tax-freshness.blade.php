@props(['compact' => false])

@php
    $freshness = $taxFreshness ?? app(\App\Services\Tax\TaxFreshness::class);
@endphp

<p {{ $attributes->class('text-sm text-ink-soft') }}>
    <span>Tax year: {{ $freshness->year() }}</span>
    <span aria-hidden="true"> · </span>
    <span>Last updated: {{ $freshness->lastUpdatedLabel() }}</span>
    @unless ($compact)
        <span class="mt-1 block">{{ $freshness->attribution() }}</span>
    @endunless
</p>
