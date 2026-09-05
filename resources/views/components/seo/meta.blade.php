@props(['seo' => null])

@php
    $title = $seo->title ?? config('app.name');
    $description = $seo->description ?? __('common.brand_tagline');
    $canonical = $seo->canonical ?? url()->current();
    $indexable = ($seo->indexable ?? true) && ! request()->has('salary');
    $ogType = $seo->ogType ?? 'website';
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $indexable ? 'index,follow' : 'noindex,nofollow' }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
