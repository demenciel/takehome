<?php

namespace App\Services\Seo;

final class SeoPage
{
    /**
     * @param  list<array{name: string, url: string}>  $breadcrumbs
     * @param  list<array{question: string, answer: string}>  $faqs
     */
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $canonical,
        public readonly bool $indexable = true,
        public readonly array $breadcrumbs = [],
        public readonly array $faqs = [],
        public readonly string $ogType = 'website',
        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly bool $includeWebsite = false,
        public readonly bool $includeApplication = true,
    ) {}
}
