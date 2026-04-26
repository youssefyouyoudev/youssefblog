@props(['seo' => []])

@php($meta = app(\App\Services\SeoService::class)->meta($seo))

<title>{{ $meta['title'] }}</title>
<meta name="description" content="{{ $meta['description'] }}">
@if ($meta['noindex'])
    <meta name="robots" content="noindex, nofollow">
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
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
<meta name="twitter:image" content="{{ $meta['image'] }}">
@foreach ($meta['schemas'] as $schema)
    <script type="application/ld+json">@json($schema)</script>
@endforeach
