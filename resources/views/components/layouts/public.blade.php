@php($seo = $seo ?? [])
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
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1914940263140841" crossorigin="anonymous"></script>
    <meta charset="utf-8">
    <script>
        (() => {
            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--bg)] font-sans text-[var(--text)] antialiased selection:bg-[var(--accent)] selection:text-white">
    <x-public.navbar />

    <main>
        {{ $slot }}
    </main>

    @unless (request()->routeIs('home') || request()->routeIs('posts.show'))
        <section class="border-t border-[var(--border)] bg-[var(--surface)]">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <x-service-cta title="Need a Laravel, SaaS, dashboard, or AI workflow?" description="Turn useful ideas from the blog into production-ready business systems with strategy, UI, Laravel engineering, deployment, and launch polish." />
            </div>
        </section>
    @endunless

    <x-public.footer />

    <button id="back-to-top" type="button" class="fixed bottom-5 right-5 z-50 hidden rounded-full border border-[var(--border)] bg-[var(--surface)] px-4 py-3 text-sm font-black text-[var(--text)] shadow-soft transition hover:-translate-y-0.5 hover:border-[var(--accent)] hover:text-[var(--accent)]">Top</button>

    <div id="cookie-consent" class="fixed inset-x-4 bottom-20 z-50 hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-4 shadow-2xl sm:left-auto sm:max-w-md">
        <p class="text-sm font-black text-[var(--text)]">Cookie consent</p>
        <p class="mt-2 text-xs leading-5 text-[var(--muted)]">We use essential cookies and may use analytics/advertising cookies after consent to improve Youssef Youyou Blog.</p>
        <button type="button" id="cookie-accept" class="premium-button mt-3 bg-[var(--text)] text-[var(--bg)]">Accept</button>
    </div>
</body>
</html>
