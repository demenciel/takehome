@props(['label', 'value', 'emphasis' => false])

<div {{ $attributes->class(['rounded-xl bg-paper p-4', 'border border-accent/30' => $emphasis]) }}>
    <p class="text-sm text-ink-soft">{{ $label }}</p>
    <p @class(['mt-1 font-semibold', 'text-3xl text-accent-dark' => $emphasis, 'text-2xl' => ! $emphasis])>{{ $value }}</p>
</div>
