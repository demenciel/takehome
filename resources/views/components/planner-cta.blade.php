@props([
    'headline',
    'copy',
    'button',
    'url',
    'clickMethod' => 'markPlannerClick',
])

@if (filled($url))
    <aside {{ $attributes->class('mt-8 rounded-2xl border border-line bg-paper p-5') }}>
        <h3 class="font-serif text-2xl text-ink">{{ $headline }}</h3>
        <p class="mt-3 text-ink-soft">{{ $copy }}</p>
        <p class="mt-4">
            <a href="{{ $url }}" wire:click="{{ $clickMethod }}" class="btn-primary inline-flex" rel="noopener noreferrer" target="_blank">
                {{ $button }}
            </a>
        </p>
        <p class="mt-2 text-sm text-ink-soft">Available on Etsy. Paycheque.app is not affiliated with Etsy or the Government of Canada.</p>
    </aside>
@endif
