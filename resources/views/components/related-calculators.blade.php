@props(['links', 'title' => 'Related calculators'])

@if (count($links))
    <section {{ $attributes->class('mx-auto max-w-6xl px-4 py-10 sm:px-6') }} aria-labelledby="related-heading">
        <h2 id="related-heading" class="font-serif text-3xl">{{ $title }}</h2>
        <ul class="mt-6 grid gap-3 sm:grid-cols-2">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['url'] }}" class="flex min-h-12 items-center rounded-xl border border-line bg-card px-4 font-semibold hover:border-accent">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
