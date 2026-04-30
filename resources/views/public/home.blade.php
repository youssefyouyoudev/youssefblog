<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
    @endphp

    <section class="hero-grid overflow-hidden bg-[#050505] text-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.04fr_.96fr] lg:px-8 lg:py-24">
            <div class="fade-up">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-24 w-auto rounded-2xl object-contain shadow-glow sm:h-32" width="320" height="144" fetchpriority="high">
                <p class="mt-8 text-xs font-black uppercase tracking-[0.24em] text-brand">Youssef Blog by Youssef Youyou</p>
                <h1 class="accent-line mt-5 max-w-5xl text-5xl font-black tracking-tight sm:text-7xl lg:text-8xl">Smart Finance, Tech & AI Guides for Builders</h1>
                <p class="mt-8 max-w-2xl text-lg leading-8 text-white/70">Practical insights from Youssef Youyou on AI tools, Laravel, SaaS, online income, and digital business systems.</p>
                <x-social-share :url="route('home')" title="Smart Finance, Tech & AI Guides" class="mt-6 text-white/75" />
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('posts.index') }}" class="premium-button bg-brand text-black">Read Latest Posts</a>
                    <a href="{{ route('services') }}" class="premium-button border border-white/20 text-white hover:border-brand hover:text-brand">Services</a>
                </div>
                <x-trust-stats class="mt-8" />
            </div>
            <div class="grid gap-5">
                <x-founder-card />
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['No hype, just practical guides', 'Morocco + global perspective', 'SEO, CRO, and systems thinking', 'Built around real business outcomes'] as $item)
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-sm font-black text-white/80 shadow-soft backdrop-blur">{{ $item }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-trust-bar />

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Popular Guides</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Popular Guides</h2>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-600">Start with focused guides grouped by reader intent, then go deeper through the full archive.</p>
            </div>
            <a href="{{ route('posts.index') }}" class="premium-button border border-black/10 bg-white text-black">All Guides</a>
        </div>
        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            @foreach ($guideGroups as [$groupTitle, $groupDescription, $posts])
                <article class="rounded-2xl border border-black/10 bg-white p-6 shadow-soft">
                    <h3 class="text-xl font-black text-ink">{{ $groupTitle }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $groupDescription }}</p>
                    <div class="mt-5 grid gap-3">
                        @foreach ($posts as $post)
                            <a href="{{ route('posts.show', $post) }}" class="rounded-xl border border-black/10 px-4 py-3 text-sm font-black text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-black">{{ $post->shortAnchorTitle() }}</a>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Latest Posts</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Latest Content</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-emerald-700">Latest guides</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-6 px-4 py-14 sm:px-6 lg:grid-cols-[.95fr_1.05fr] lg:px-8">
        <x-newsletter-box />
        <x-service-cta title="Need Development Services?" description="Work with Youssef Youyou on a premium build that connects strategy, design, Laravel engineering, automation, and deployment." />
    </section>
</x-layouts.public>
