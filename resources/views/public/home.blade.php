<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $hubs = [
            ['Finance', 'Money systems, savings, fintech, freelancer finance.', route('categories.show', 'finance')],
            ['AI', 'Tools, workflows, automation, agents, productivity.', route('categories.show', 'ai')],
            ['Tech', 'Hosting, gear, productivity, cybersecurity, systems.', route('categories.show', 'tech')],
            ['Online Income', 'Practical digital income and service business ideas.', route('posts.index')],
            ['Morocco', 'Guides for Moroccan freelancers and business owners.', route('posts.index')],
            ['Developer', 'Laravel, deployment, SaaS, dashboards, and APIs.', route('categories.show', 'laravel')],
        ];
    @endphp

    <section class="overflow-hidden border-b border-black/10 bg-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.08fr_.92fr] lg:px-8 lg:py-20">
            <div>
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-24 w-auto rounded-xl object-contain shadow-sm sm:h-32" width="320" height="144" fetchpriority="high">
                <p class="mt-8 text-sm font-black uppercase tracking-wide text-emerald-600">Morocco-born media for builders</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-tight text-black sm:text-6xl">Smart Finance, Tech & AI Guides for Morocco & Global Readers</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Actionable content on money, online business, AI tools, tech trends, and growth strategies.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('posts.index') }}" class="rounded-lg bg-black px-6 py-3 text-center text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-900">Start Reading</a>
                    <a href="{{ route('services') }}" class="rounded-lg border border-black/10 bg-brand px-6 py-3 text-center text-sm font-black text-black shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-300">Work With Youssef</a>
                </div>
                <div class="mt-8 grid gap-3 text-sm font-bold text-slate-600 sm:grid-cols-3">
                    @foreach ($brand['stats'] as $stat)
                        <span class="rounded-lg border border-black/10 bg-white px-4 py-3 shadow-sm">{{ $stat }}</span>
                    @endforeach
                </div>
            </div>
            <div class="rounded-lg border border-black/10 bg-black p-6 text-white shadow-2xl">
                <p class="text-sm font-black uppercase tracking-wide text-brand">Work with Youssef</p>
                <h2 class="mt-3 text-3xl font-black">Premium full-stack delivery for serious businesses.</h2>
                <p class="mt-4 text-sm leading-6 text-white/70">Websites, SaaS platforms, dashboards, CRM/ERP systems, APIs, automation layers, AI workflows, and Laravel deployment built to win trust fast.</p>
                <div class="mt-6 grid gap-3">
                    <a href="{{ $brand['portfolio_url'] }}" class="rounded-lg border border-white/10 bg-white px-4 py-3 text-sm font-black text-black transition hover:bg-brand">View Portfolio</a>
                    <a href="{{ $brand['whatsapp_url'] }}" class="rounded-lg border border-white/10 px-4 py-3 text-sm font-black text-white transition hover:border-brand hover:text-brand">Contact / WhatsApp</a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-black uppercase text-emerald-600">Featured Posts</p>
                <h2 class="mt-2 text-3xl font-black">Editor-selected guides</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-emerald-700">All posts</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ($featuredPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="border-y border-black/10 bg-slate-50">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
            <div>
                <h2 class="text-3xl font-black">Trending Posts</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($trendingPosts as $post)
                        <a href="{{ route('posts.show', $post) }}" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-500 hover:shadow-lg">
                            <span class="text-xs font-black uppercase text-emerald-600">{{ $post->category->name }}</span>
                            <h3 class="mt-2 text-lg font-black">{{ $post->title }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $post->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <x-ad-slot-top label="Homepage sidebar ad" />
                <div class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <h2 class="text-xl font-black">Popular This Week</h2>
                    <div class="mt-4 grid gap-4">
                        @foreach ($popularPosts as $post)
                            <a href="{{ route('posts.show', $post) }}" class="border-b border-black/10 pb-4 last:border-b-0 last:pb-0">
                                <span class="text-xs font-black uppercase text-slate-500">{{ $post->readingMinutes() }} min read</span>
                                <span class="mt-1 block font-black hover:text-emerald-700">{{ $post->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-black">Category Blocks</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($hubs as [$name, $description, $url])
                <a href="{{ $url }}" class="rounded-lg border border-black/10 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:bg-emerald-50 hover:shadow-lg">
                    <span class="text-xl font-black">{{ $name }}</span>
                    <span class="mt-3 block text-sm leading-6 text-slate-600">{{ $description }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase text-emerald-600">Affiliate Guides</p>
                    <h2 class="mt-2 text-3xl font-black">Money pages built for helpful comparisons</h2>
                </div>
                <a href="{{ route('money.index') }}" class="text-sm font-black text-emerald-700">View all</a>
            </div>
            <x-affiliate-disclosure class="mt-6" />
            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($moneyPages as $page)
                    <a href="{{ route('money.show', $page['slug']) }}" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-lg">
                        <span class="text-xs font-black uppercase text-emerald-600">{{ $page['category'] }}</span>
                        <span class="mt-2 block text-lg font-black">{{ $page['title'] }}</span>
                        <span class="mt-3 block text-sm leading-6 text-slate-600">{{ $page['excerpt'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <x-ad-slot-middle label="Homepage display ad placeholder" />
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-black uppercase text-emerald-600">Latest Posts</p>
                <h2 class="mt-2 text-3xl font-black">Fresh guides for builders</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-emerald-700">Read latest</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
            <x-newsletter-card />
            <x-hire-youssef-banner />
        </div>
    </section>
</x-layouts.public>
