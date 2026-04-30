@php
    $brand = config('brand');
    $links = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Articles', 'url' => route('posts.index'), 'active' => request()->routeIs('posts.*') || request()->routeIs('tags.show')],
        ['label' => 'Categories', 'url' => route('home').'#categories', 'active' => request()->routeIs('categories.*')],
        ['label' => 'About', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
        ['label' => 'Work With Me', 'url' => route('services'), 'active' => request()->routeIs('services') || request()->routeIs('services.alias')],
    ];
    $mobileLinks = [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Articles', 'url' => route('posts.index')],
        ['label' => 'Categories', 'url' => route('home').'#categories'],
        ['label' => 'Laravel', 'url' => route('categories.show', 'laravel')],
        ['label' => 'AI', 'url' => route('categories.show', 'ai')],
        ['label' => 'Business', 'url' => route('categories.show', 'business')],
        ['label' => 'Tools', 'url' => route('tools.index')],
        ['label' => 'About', 'url' => route('about')],
        ['label' => 'Contact', 'url' => route('contact')],
    ];
@endphp

<header id="site-header" class="site-header-glass sticky top-0 z-50 border-b border-[var(--border)] backdrop-blur-xl transition-shadow duration-200">
    <div class="safe-container flex min-h-[68px] items-center justify-between gap-3 py-3">
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3" aria-label="Youssef Youyou Blog home">
            <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Youyou Blog" class="h-10 w-10 shrink-0 rounded-xl object-cover ring-1 ring-[var(--border)] transition group-hover:ring-[var(--accent)] sm:h-11 sm:w-11" width="44" height="44">
            <span class="min-w-0">
                <span class="block max-w-[145px] truncate text-sm font-black tracking-tight text-[var(--text)] min-[375px]:max-w-[175px] sm:max-w-none sm:text-base">Youssef Youyou Blog</span>
                <span class="hidden text-xs font-bold text-[var(--muted)] sm:block">Laravel, SaaS, AI & business systems</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 rounded-full border border-[var(--border)] bg-[var(--surface)]/90 p-1 text-sm font-bold shadow-sm xl:flex" aria-label="Primary navigation">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="min-h-11 rounded-full px-4 py-2.5 transition hover:bg-[var(--accent-soft)] hover:text-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] {{ $link['active'] ? 'bg-[var(--text)] text-[var(--bg)] hover:bg-[var(--text)] hover:text-[var(--bg)]' : 'text-[var(--muted)]' }}">{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 xl:flex">
            <a href="{{ route('posts.index') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-[var(--border)] bg-[var(--surface)] text-[var(--muted)] shadow-sm transition hover:border-[var(--accent)] hover:text-[var(--accent)]" aria-label="Search articles">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
            </a>
            <x-public.theme-toggle />
            <a href="{{ route('services') }}" class="premium-button bg-[var(--text)] text-[var(--bg)]">Start a Project</a>
        </div>

        <div class="flex items-center gap-2 xl:hidden">
            <x-public.theme-toggle />
            <button type="button" data-mobile-menu-button class="inline-flex h-11 min-w-11 items-center justify-center gap-2 rounded-full border border-[var(--border)] bg-[var(--surface)] px-3 text-sm font-black text-[var(--text)] shadow-sm min-[390px]:px-4" aria-controls="mobile-menu" aria-expanded="false" aria-label="Open menu">
                <span class="hidden min-[390px]:inline">Menu</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" data-mobile-menu class="mobile-menu-panel hidden border-t border-[var(--border)] bg-[var(--bg)] shadow-2xl xl:hidden">
        <nav class="safe-container grid gap-2 py-4 text-sm font-bold" aria-label="Mobile navigation">
            @foreach ($mobileLinks as $link)
                <a href="{{ $link['url'] }}" data-mobile-menu-link class="flex min-h-11 items-center rounded-2xl border border-[var(--border)] bg-[var(--surface)] px-4 py-3 text-[var(--text)] transition hover:border-[var(--accent)] hover:text-[var(--accent)]">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('services') }}" data-mobile-menu-link class="premium-button mt-2 bg-[var(--accent)] text-white">Work With Me</a>
        </nav>
    </div>
</header>
