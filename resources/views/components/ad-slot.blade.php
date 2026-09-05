@props(['name' => null, 'placement' => null])

@php
    $aliases = [
        'top' => 'top',
        'middle' => 'middle',
        'bottom' => 'bottom',
    ];

    $slot = $name ?? ($aliases[$placement ?? ''] ?? $placement ?? 'middle');
    $enabled = config('ads.enabled') && data_get(config('ads.slots'), $slot.'.enabled', false);
    $provider = config('ads.provider');
@endphp

@unless ($enabled)
    @return
@endunless

<aside {{ $attributes->class('ad-slot my-8 rounded-xl border border-dashed border-line bg-white/60 p-4 text-center text-sm text-ink-soft') }} aria-label="Advertisement">
    @if ($provider === 'adsense' && config('ads.client'))
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="{{ config('ads.client') }}"
             data-ad-slot="{{ $slot }}"
             data-ad-format="{{ data_get(config('ads.slots'), $slot.'.format', 'auto') }}"></ins>
    @else
        <p>Ad slot: {{ $slot }}</p>
    @endif
</aside>
