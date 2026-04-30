@php
    $brand = config('brand');
    $links = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Articles', 'url' => route('posts.index'), 'active' => request()->routeIs('posts.*') || request()->routeIs('tags.show')],
        ['label' => 'Categories', 'url' => route('home').'#categories', 'active' => request()->routeIs('categories.*')],
        ['label' => 'About', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
    ];
@endphp

<header id="site-header" class="sticky top-0 z-50 border-b border-[var(--border)] bg-[var(--bg)] backdrop-blur-xl transition-shadow duration-200">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3" aria-label="Youssef Youyou Blog home">
            <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Youyou Blog" class="h-11 w-11 rounded-xl object-cover ring-1 ring-[var(--border)] transition group-hover:ring-[var(--accent)]" width="44" height="44">
            <span class="min-w-0">
                <span class="block truncate text-sm font-black tracking-tight text-[var(--text)] sm:text-base">Youssef Youyou Blog</span>
                <span class="hidden text-xs font-bold text-[var(--muted)] sm:block">Laravel, SaaS, AI & business systems</span>
            </span>
        </a>

        <nav class="hidden items-center gap-1 rounded-full border border-[var(--border)] bg-[var(--surface)] p-1 text-sm font-bold shadow-sm lg:flex" aria-label="Primary navigation">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="rounded-full px-4 py-2 transition hover:bg-[var(--accent-soft)] hover:text-[var(--accent)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] {{ $link['active'] ? 'bg-[var(--text)] text-[var(--bg)] hover:bg-[var(--text)] hover:text-[var(--bg)]' : 'text-[var(--muted)]' }}">{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-2 lg:flex">
            <x-public.theme-toggle />
            <a href="{{ $brand['portfolio_url'] }}" class="premium-button bg-[var(--text)] text-[var(--bg)]" rel="noopener noreferrer">Work with Youssef</a>
        </div>

        <div class="flex items-center gap-2 lg:hidden">
            <x-public.theme-toggle />
            <button type="button" data-mobile-menu-button class="inline-flex h-11 items-center gap-2 rounded-full border border-[var(--border)] bg-[var(--surface)] px-4 text-sm font-black text-[var(--text)] shadow-sm" aria-controls="mobile-menu" aria-expanded="false">
                Menu
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" data-mobile-menu class="hidden border-t border-[var(--border)] bg-[var(--bg)] px-4 py-4 shadow-2xl lg:hidden">
        <nav class="mx-auto grid max-w-7xl gap-2 text-sm font-bold" aria-label="Mobile navigation">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] px-4 py-3 text-[var(--text)] transition hover:border-[var(--accent)] hover:text-[var(--accent)]">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ $brand['portfolio_url'] }}" class="premium-button mt-2 bg-[var(--accent)] text-white" rel="noopener noreferrer">Work with Youssef</a>
        </nav>
    </div>
</header>
