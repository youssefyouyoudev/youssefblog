<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $categoryMap = $categories->keyBy('slug');
        $hubs = [
            ['Finance', 'Money systems, freelancer finance, fintech tools, and online income basics.', route('categories.show', 'finance'), $categoryMap->get('finance')?->posts_count, 'chart'],
            ['Tech', 'Hosting, devices, productivity, cybersecurity, and digital infrastructure.', route('categories.show', 'tech'), $categoryMap->get('tech')?->posts_count, 'cpu'],
            ['AI', 'AI tools, agents, workflows, automation, and practical business use cases.', route('categories.show', 'ai'), $categoryMap->get('ai')?->posts_count, 'sparkle'],
            ['Laravel', 'SEO, deployment, performance, security, SaaS ideas, and backend systems.', route('categories.show', 'laravel'), $categoryMap->get('laravel')?->posts_count, 'code'],
            ['Business', 'SaaS ideas, client acquisition, digital services, and Morocco-ready growth.', route('categories.show', 'business'), $categoryMap->get('business')?->posts_count, 'briefcase'],
        ];
        $leadPost = $featuredPosts->first();
        $sideFeatured = $featuredPosts->skip(1)->take(3);
    @endphp

    <section class="hero-grid overflow-hidden bg-[#050505] text-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.04fr_.96fr] lg:px-8 lg:py-24">
            <div class="fade-up">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-24 w-auto rounded-2xl object-contain shadow-glow sm:h-32" width="320" height="144" fetchpriority="high">
                <p class="mt-8 text-xs font-black uppercase tracking-[0.24em] text-brand">Youssef Blog by Youssef Youyou</p>
                <h1 class="accent-line mt-5 max-w-5xl text-5xl font-black tracking-tight sm:text-7xl lg:text-8xl">Smart Finance, Tech & AI Guides for Builders</h1>
                <p class="mt-8 max-w-2xl text-lg leading-8 text-white/70">Practical insights from Youssef Youyou on AI tools, Laravel, SaaS, online income, and digital business systems.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('posts.index') }}" class="premium-button bg-brand text-black">Read Latest Posts</a>
                    <a href="{{ route('services') }}" class="premium-button border border-white/20 text-white hover:border-brand hover:text-brand">Work With Youssef</a>
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
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Featured Articles</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Editorial picks for serious builders</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="premium-button border border-black/10 bg-white text-black">View All</a>
        </div>
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
            @if ($leadPost)
                <x-featured-post-card :post="$leadPost" large />
            @endif
            <div class="grid gap-6">
                @foreach ($sideFeatured as $post)
                    <x-featured-post-card :post="$post" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Trending Now</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">What readers are opening next</h2>
                <div class="mt-8 grid gap-4 md:grid-cols-2">
                    @foreach ($trendingPosts as $post)
                        <a href="{{ route('posts.show', $post) }}" class="group rounded-2xl border border-black/10 bg-white p-5 shadow-soft transition duration-300 hover:-translate-y-1 hover:border-emerald-500 hover:shadow-glow">
                            <span class="category-pill">{{ $post->category->name }}</span>
                            <h3 class="mt-4 text-lg font-black text-ink group-hover:text-emerald-700">{{ $post->title }}</h3>
                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $post->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <x-ad-slot />
                <div class="rounded-2xl border border-black/10 bg-[#050505] p-6 text-white shadow-glow">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-brand">Popular This Week</p>
                    <div class="mt-5 grid gap-4">
                        @foreach ($popularPosts as $post)
                            <a href="{{ route('posts.show', $post) }}" class="border-b border-white/10 pb-4 last:border-b-0 last:pb-0">
                                <span class="text-xs font-black uppercase text-white/45">{{ $post->readingMinutes() }} min read</span>
                                <span class="mt-1 block font-black text-white transition hover:text-brand">{{ $post->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Category Hubs</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Choose your advantage</h2>
            </div>
        </div>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($hubs as [$name, $description, $url, $count, $icon])
                <x-category-card :name="$name" :description="$description" :url="$url" :count="$count" :icon="$icon" />
            @endforeach
        </div>
    </section>

    <section class="bg-[#0B0F0A] text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand">Why Trust This Blog?</p>
                    <h2 class="mt-3 text-3xl font-black">Built for readers who value signal.</h2>
                </div>
                @foreach ([
                    ['Built by a real developer', 'Youssef builds production websites, SaaS platforms, dashboards, APIs, and AI-enabled workflows.'],
                    ['Practical business focus', 'Guides connect tools and tactics to revenue, trust, speed, and operational clarity.'],
                    ['Morocco + global perspective', 'Content is useful for Moroccan freelancers and international builders alike.'],
                ] as [$title, $copy])
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-soft">
                        <h3 class="text-xl font-black">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-white/65">{{ $copy }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Latest Posts</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Fresh guides from the desk</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-emerald-700">Read latest</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Money Pages</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Recommended guides before you buy</h2>
                </div>
                <a href="{{ route('money.index') }}" class="text-sm font-black text-emerald-700">All comparisons</a>
            </div>
            <x-affiliate-disclosure class="mt-6" />
            <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($moneyPages as $page)
                    <x-money-guide-card :page="$page" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-6 px-4 py-14 sm:px-6 lg:grid-cols-[.95fr_1.05fr] lg:px-8">
        <x-newsletter-box />
        <x-service-cta title="Need a website, SaaS platform, dashboard, or AI workflow?" description="Work with Youssef Youyou on a premium build that connects strategy, design, Laravel engineering, automation, and deployment." />
    </section>
</x-layouts.public>
