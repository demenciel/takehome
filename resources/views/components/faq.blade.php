@props(['faqs'])

@if (count($faqs))
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6" aria-labelledby="faq-heading">
        <h2 id="faq-heading" class="font-serif text-3xl text-ink">Frequently asked questions</h2>
        <div class="mt-8 divide-y divide-line border-y border-line">
            @foreach ($faqs as $faq)
                <details class="group py-4">
                    <summary class="cursor-pointer list-none text-lg font-semibold text-ink marker:content-none">
                        <span class="flex items-start justify-between gap-4">
                            {{ $faq['question'] }}
                            <span aria-hidden="true" class="text-ink-soft group-open:hidden">+</span>
                            <span aria-hidden="true" class="hidden text-ink-soft group-open:inline">−</span>
                        </span>
                    </summary>
                    <p class="mt-3 max-w-2xl text-ink-soft">{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>
@endif
