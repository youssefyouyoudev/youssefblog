@php
    $seo = $seo ?? [];
    $title = $seo['title'] ?? 'Youssef Blog | Finance, Tech & AI';
    $description = $seo['description'] ?? 'Smart finance, tech, AI, Laravel, and online business guides for builders.';
    $canonical = $seo['canonical'] ?? url()->current();
    $image = $seo['image'] ?? asset('assets/brand/youssef-blog-og.png');
    $type = $seo['type'] ?? 'website';
    $keywords = $seo['keywords'] ?? null;
    $noindex = $seo['noindex'] ?? false;
    $brand = config('brand');
    $navLinks = [
        ['Home', route('home')],
        ['Finance', route('categories.show', 'finance')],
        ['Tech', route('categories.show', 'tech')],
        ['AI', route('categories.show', 'ai')],
        ['Laravel', route('categories.show', 'laravel')],
        ['Business', route('categories.show', 'business')],
        ['Tools', route('tools.index')],
        ['Work With Me', route('services')],
    ];
    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $brand['name'],
        'url' => $brand['portfolio_url'],
        'logo' => asset('assets/brand/youssef-blog-logo.png'),
        'email' => $brand['email'],
        'telephone' => $brand['phone'],
        'founder' => ['@type' => 'Person', 'name' => $brand['name']],
        'sameAs' => array_values($brand['social']),
    ];
    $personSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $brand['name'],
        'url' => $brand['portfolio_url'],
        'jobTitle' => 'Senior Full-Stack Developer',
        'email' => $brand['email'],
        'telephone' => $brand['phone'],
        'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'MA'],
    ];
    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $brand['blog_name'],
        'url' => url('/'),
        'publisher' => ['@type' => 'Organization', 'name' => $brand['name']],
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    @if ($noindex)
        <meta name="robots" content="noindex, nofollow">
    @endif
    @if ($keywords)
        <meta name="keywords" content="{{ $keywords }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <link rel="preload" as="image" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $image }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">
    <script type="application/ld+json">@json($organizationSchema)</script>
    <script type="application/ld+json">@json($personSchema)</script>
    <script type="application/ld+json">@json($websiteSchema)</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8faf9] font-sans antialiased">
    <header class="sticky top-0 z-50 border-b border-black/10 bg-white/85 shadow-sm backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-12 w-auto rounded-lg object-contain" width="88" height="48">
                <span class="hidden sm:block">
                    <span class="block text-base font-black tracking-tight text-ink">{{ $brand['blog_name'] }}</span>
                    <span class="block text-xs font-bold uppercase tracking-wide text-slate-500">By {{ $brand['name'] }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 rounded-full border border-black/10 bg-white px-2 py-2 text-sm font-bold text-slate-700 shadow-sm lg:flex">
                @foreach ($navLinks as [$label, $url])
                    <a href="{{ $url }}" class="rounded-full px-3 py-2 transition hover:bg-emerald-50 hover:text-emerald-700 {{ url()->current() === $url ? 'bg-black text-white hover:bg-black hover:text-white' : '' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                <a href="{{ route('posts.index') }}" aria-label="Search posts" class="flex h-11 w-11 items-center justify-center rounded-full border border-black/10 bg-white text-ink transition hover:border-emerald-500 hover:text-emerald-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                </a>
                <a href="{{ route('services') }}" class="premium-button bg-black text-white">Work With Me</a>
            </div>

            <details class="group relative lg:hidden">
                <summary class="flex list-none items-center gap-2 rounded-full border border-black/10 bg-white px-4 py-2 text-sm font-black shadow-sm">
                    Menu
                    <span class="block h-2 w-2 rounded-full bg-brand"></span>
                </summary>
                <nav class="absolute right-0 mt-3 grid w-72 gap-1 rounded-2xl border border-black/10 bg-white p-3 text-sm font-bold shadow-2xl">
                    @foreach ($navLinks as [$label, $url])
                        <a class="rounded-xl px-3 py-3 transition hover:bg-emerald-50 hover:text-emerald-700" href="{{ $url }}">{{ $label }}</a>
                    @endforeach
                    <a class="rounded-xl px-3 py-3 transition hover:bg-emerald-50 hover:text-emerald-700" href="{{ route('money.index') }}">Best Comparisons</a>
                    <a class="rounded-xl bg-black px-3 py-3 text-white transition hover:bg-emerald-600" href="{{ $brand['whatsapp_url'] }}">WhatsApp Now</a>
                </nav>
            </details>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <section class="border-t border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <x-service-cta title="Need a website, SaaS platform, dashboard, or AI workflow?" description="Turn useful ideas from the blog into production-ready business systems: strategy, UI, Laravel backend, deployment, and launch polish." />
        </div>
    </section>

    <footer class="border-t border-black/10 bg-[#050505] text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-5 lg:px-8">
            <div class="md:col-span-2">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-20 w-auto rounded-xl object-contain" width="180" height="80">
                <p class="mt-4 max-w-md text-sm leading-6 text-white/70">{{ $brand['insights'] }} {{ $brand['positioning'] }}</p>
                <div class="mt-6 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="mailto:{{ $brand['email'] }}">{{ $brand['email'] }}</a>
                    <a class="hover:text-brand" href="{{ $brand['whatsapp_url'] }}">WhatsApp {{ $brand['phone'] }}</a>
                    <span>{{ $brand['location'] }} + international work</span>
                </div>
            </div>
            <div>
                <p class="font-black text-white">Categories</p>
                <div class="mt-4 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="{{ route('categories.show', 'finance') }}">Finance</a>
                    <a class="hover:text-brand" href="{{ route('categories.show', 'tech') }}">Tech</a>
                    <a class="hover:text-brand" href="{{ route('categories.show', 'ai') }}">AI</a>
                    <a class="hover:text-brand" href="{{ route('categories.show', 'laravel') }}">Laravel</a>
                    <a class="hover:text-brand" href="{{ route('categories.show', 'business') }}">Business</a>
                </div>
            </div>
            <div>
                <p class="font-black text-white">Services</p>
                <div class="mt-4 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="{{ route('services') }}">Work With Me</a>
                    <a class="hover:text-brand" href="{{ $brand['portfolio_url'] }}">Portfolio</a>
                    <a class="hover:text-brand" href="{{ $brand['services_url'] }}">Services</a>
                    <a class="hover:text-brand" href="{{ $brand['case_studies_url'] }}">Case Studies</a>
                    <a class="hover:text-brand" href="{{ route('money.index') }}">Best Comparisons</a>
                </div>
            </div>
            <div>
                <p class="font-black text-white">Legal</p>
                <div class="mt-4 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="{{ route('about') }}">About</a>
                    <a class="hover:text-brand" href="{{ route('contact') }}">Contact</a>
                    <a class="hover:text-brand" href="{{ route('privacy') }}">Privacy Policy</a>
                    <a class="hover:text-brand" href="{{ route('terms') }}">Terms</a>
                    <a class="hover:text-brand" href="{{ route('editorial-policy') }}">Editorial Policy</a>
                    <a class="hover:text-brand" href="{{ route('affiliate-disclosure') }}">Affiliate Disclosure</a>
                    <a class="hover:text-brand" href="{{ route('sitemap') }}">Sitemap</a>
                </div>
            </div>
            <div class="md:col-span-5 border-t border-white/10 pt-6 text-xs text-white/50">
                2026 {{ $brand['name'] }}. {{ $brand['tagline'] }} Youssef Blog is the media arm of the Youssef Youyou professional brand.
            </div>
        </div>
    </footer>
</body>
</html>
