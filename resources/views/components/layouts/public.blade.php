@php
    $seo = $seo ?? [];
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
    $socialLinks = collect($brand['social'] ?? [])->filter();
    $socialLabels = [
        'github' => 'GitHub',
        'linkedin' => 'LinkedIn',
        'twitter' => 'X',
        'youtube' => 'YouTube',
        'instagram' => 'Instagram',
    ];
@endphp
<!doctype html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-8RGCVFSS9K"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-8RGCVFSS9K');
</script>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1914940263140841"
     crossorigin="anonymous"></script>
    <meta charset="utf-8">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo :seo="$seo" />
    <link rel="icon" type="image/png" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <link rel="preload" as="image" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://images.unsplash.com">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8faf9] font-sans antialiased">
    <header id="site-header" class="sticky top-0 z-50 border-b border-black/10 bg-white/85 backdrop-blur-xl transition-shadow duration-200 dark:bg-slate-950/85">
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
                <button type="button" id="theme-toggle" class="flex h-11 w-11 items-center justify-center rounded-full border border-black/10 bg-white text-ink transition hover:border-blue-600 hover:text-blue-600" aria-label="Toggle dark mode">
                    <svg id="theme-moon" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.5 6.5 0 0 0 21 12.8Z"/></svg>
                    <svg id="theme-sun" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
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
                    <a class="rounded-xl bg-black px-3 py-3 text-white transition hover:bg-emerald-600" href="{{ $brand['whatsapp_url'] }}" rel="noopener noreferrer">WhatsApp Now</a>
                </nav>
            </details>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    @unless (request()->routeIs('home'))
    <section class="border-t border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <x-service-cta title="Need a website, SaaS platform, dashboard, or AI workflow?" description="Turn useful ideas from the blog into production-ready business systems: strategy, UI, Laravel backend, deployment, and launch polish." />
        </div>
    </section>
    @endunless

    <footer class="border-t border-black/10 bg-[#050505] text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-5 lg:px-8">
            <div class="md:col-span-2">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-20 w-auto rounded-xl object-contain" width="180" height="80">
                <p class="mt-4 max-w-md text-sm leading-6 text-white/70">{{ $brand['insights'] }} {{ $brand['positioning'] }}</p>
                <div class="mt-6 grid gap-2 text-sm text-white/70">
                    <a class="hover:text-brand" href="mailto:{{ $brand['email'] }}">{{ $brand['email'] }}</a>
                    <a class="hover:text-brand" href="{{ $brand['whatsapp_url'] }}" rel="noopener noreferrer">WhatsApp {{ $brand['phone'] }}</a>
                    <span>{{ $brand['location'] }} + international work</span>
                </div>
                @if ($socialLinks->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-3 text-sm font-bold text-white/70">
                        @foreach ($socialLinks as $network => $url)
                            <a class="hover:text-brand" href="{{ $url }}" rel="noopener noreferrer me" target="_blank">{{ $socialLabels[$network] ?? Str::headline($network) }}</a>
                        @endforeach
                    </div>
                @endif
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
                    <a class="hover:text-brand" href="{{ $brand['portfolio_url'] }}" rel="noopener noreferrer">Portfolio</a>
                    <a class="hover:text-brand" href="{{ $brand['services_url'] }}" rel="noopener noreferrer">Services</a>
                    <a class="hover:text-brand" href="{{ $brand['case_studies_url'] }}" rel="noopener noreferrer">Case Studies</a>
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
    <button id="back-to-top" type="button" class="fixed bottom-5 right-5 z-50 hidden rounded-full bg-black px-4 py-3 text-sm font-black text-brand shadow-glow">Top</button>
    <div id="cookie-consent" class="fixed inset-x-4 bottom-20 z-50 hidden rounded-2xl border border-black/10 bg-white p-4 shadow-2xl sm:left-auto sm:max-w-md">
        <p class="text-sm font-black text-ink">Cookie consent</p>
        <p class="mt-2 text-xs leading-5 text-slate-600">We use essential cookies and may use analytics/advertising cookies after consent to improve Youssef Blog.</p>
        <button type="button" id="cookie-accept" class="premium-button mt-3 bg-black text-white">Accept</button>
    </div>
    <script>
        const backToTop = document.getElementById('back-to-top');
        const themeToggle = document.getElementById('theme-toggle');
        const siteHeader = document.getElementById('site-header');
        const themeMoon = document.getElementById('theme-moon');
        const themeSun = document.getElementById('theme-sun');
        const cookieConsent = document.getElementById('cookie-consent');
        const cookieAccept = document.getElementById('cookie-accept');

        window.addEventListener('scroll', () => {
            if (!backToTop) return;
            backToTop.classList.toggle('hidden', window.scrollY < 300);
            siteHeader?.classList.toggle('shadow-lg', window.scrollY > 10);
        }, { passive: true });

        const syncThemeIcon = () => {
            const isDark = document.documentElement.classList.contains('dark');
            themeMoon?.classList.toggle('hidden', isDark);
            themeSun?.classList.toggle('hidden', !isDark);
        };
        syncThemeIcon();
        backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        themeToggle?.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            syncThemeIcon();
        });

        if (!document.cookie.includes('yb_cookie_consent=accepted')) cookieConsent?.classList.remove('hidden');
        cookieAccept?.addEventListener('click', () => {
            document.cookie = 'yb_cookie_consent=accepted; max-age=31536000; path=/; SameSite=Lax';
            cookieConsent?.classList.add('hidden');
        });
    </script>
</body>
</html>
