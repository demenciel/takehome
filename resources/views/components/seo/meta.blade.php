@props(['seo' => null])

@php
    $title = $seo->title ?? config('app.name');
    $description = $seo->description ?? __('common.brand_tagline');
    $canonical = $seo->canonical ?? url()->current();
    $indexable = ($seo->indexable ?? true) && request()->query() === [];
    $ogType = $seo->ogType ?? 'website';
    $ogTitle = $seo->ogTitle ?? $title;
    $ogDescription = $seo->ogDescription ?? $description;
    $ogImage = config('seo.default_og_image');
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $indexable ? 'index,follow' : 'noindex,follow' }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
@if (config('seo.twitter_handle'))
    <meta name="twitter:site" content="{{ config('seo.twitter_handle') }}">
@endif
