@props(['seo'])

@php
    $graph = [
        [
            '@type' => 'WebApplication',
            'name' => config('app.name'),
            'url' => $seo->canonical,
            'applicationCategory' => 'FinanceApplication',
            'operatingSystem' => 'Any',
            'description' => $seo->description,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => config('tax.currency'),
            ],
        ],
    ];

    if ($seo->breadcrumbs !== []) {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($seo->breadcrumbs)->values()->map(fn ($crumb, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->all(),
        ];
    }

    if ($seo->faqs !== []) {
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => collect($seo->faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ])->all(),
        ];
    }
@endphp

@php
    $payload = [
        '@'.'context' => 'https://schema.org',
        '@graph' => $graph,
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
