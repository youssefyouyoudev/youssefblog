@props(['seo' => []])

@php($meta = app(\App\Services\SeoService::class)->meta($seo))

<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
@if ($meta['noindex'])
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif
@if ($meta['keywords'])
    <meta name="keywords" content="{{ $meta['keywords'] }}">
@endif
<link rel="canonical" href="{{ $meta['canonical'] }}">
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['description'] }}">
<meta property="og:type" content="{{ $meta['type'] }}">
<meta property="og:url" content="{{ $meta['canonical'] }}">
<meta property="og:image" content="{{ $meta['image'] }}">
@if ($meta['type'] === 'article')
    <meta property="article:author" content="{{ $meta['author'] }}">
    @if ($meta['published_time'])
        <meta property="article:published_time" content="{{ $meta['published_time'] }}">
    @endif
    @if ($meta['modified_time'])
        <meta property="article:modified_time" content="{{ $meta['modified_time'] }}">
    @endif
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
<meta name="twitter:image" content="{{ $meta['image'] }}">
@foreach ($meta['schemas'] as $schema)
    <script type="application/ld+json">@json($schema)</script>
@endforeach
