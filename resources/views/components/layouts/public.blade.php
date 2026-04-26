@php
    $seo = $seo ?? [];
    $title = $seo['title'] ?? 'Youssef Blog | Finance, Tech & AI';
    $description = $seo['description'] ?? 'Smart finance, tech, AI, Laravel, and online business guides for builders.';
    $canonical = $seo['canonical'] ?? url()->current();
    $image = $seo['image'] ?? asset('assets/brand/youssef-blog-og.png');
    $type = $seo['type'] ?? 'website';
    $keywords = $seo['keywords'] ?? null;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @if ($keywords)
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $image }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <header class="sticky top-0 z-50 border-b border-black/10 bg-white/90 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-11 w-auto rounded-md object-contain" width="80" height="44">
                <span>
                    <span class="block text-base font-black tracking-tight">Youssef Blog</span>
                    <span class="block text-xs font-semibold uppercase text-slate-500">Finance . Tech . AI</span>
                </span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-700 md:flex">
                <a class="transition hover:text-emerald-700" href="{{ route('posts.index') }}">Posts</a>
                <a class="transition hover:text-emerald-700" href="{{ route('categories.show', 'finance') }}">Finance</a>
                <a class="transition hover:text-emerald-700" href="{{ route('categories.show', 'ai') }}">AI</a>
                <a class="transition hover:text-emerald-700" href="{{ route('categories.show', 'laravel') }}">Laravel</a>
                <a class="transition hover:text-emerald-700" href="{{ route('about') }}">About</a>
            </nav>
            <a href="{{ route('posts.index') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-900">Read Latest</a>
        </div>
    </header>
    <main>
        {{ $slot }}
    </main>
    <footer class="border-t border-black/10 bg-black text-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-4 lg:px-8">
            <div class="md:col-span-2">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-20 w-auto rounded-lg object-contain" width="180" height="80">
                <p class="mt-3 max-w-md text-sm leading-6 text-white/70">Smart finance, technology, AI, Laravel, and online business guides for builders who like clear thinking and useful systems.</p>
            </div>
            <div>
                <p class="font-bold">Explore</p>
                <div class="mt-3 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="{{ route('posts.index') }}">Posts</a>
                    <a class="hover:text-brand" href="{{ route('about') }}">About</a>
                    <a class="hover:text-brand" href="{{ route('contact') }}">Contact</a>
                    <a class="hover:text-brand" href="{{ route('feed') }}">RSS Feed</a>
                </div>
            </div>
            <div>
                <p class="font-bold">Legal</p>
                <div class="mt-3 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="{{ route('privacy') }}">Privacy Policy</a>
                    <a class="hover:text-brand" href="{{ route('terms') }}">Terms</a>
                    <a class="hover:text-brand" href="{{ route('sitemap') }}">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
