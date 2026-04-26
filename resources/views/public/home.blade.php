<x-layouts.public :seo="$seo">
    <section class="overflow-hidden bg-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-20">
            <div>
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-28 w-auto rounded-xl object-contain shadow-sm sm:h-36" width="320" height="144">
                <p class="mt-8 text-sm font-black uppercase tracking-wide text-emerald-600">Finance . Tech . AI . Laravel . Business</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-tight text-black sm:text-6xl">Smart Finance, Tech & AI Guides for Builders</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Practical 2026 guides for saving money, using AI tools, launching Laravel products, and building online income with a clean, beginner-friendly system.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('posts.index') }}" class="rounded-lg bg-black px-6 py-3 text-center text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-900">Read Latest Posts</a>
                    <a href="{{ route('categories.show', 'ai') }}" class="rounded-lg border border-black/10 bg-brand px-6 py-3 text-center text-sm font-black text-black shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-300">Explore AI Guides</a>
                </div>
            </div>
            <div class="rounded-lg border border-black/10 bg-black p-6 text-white shadow-2xl">
                <p class="text-sm font-black uppercase tracking-wide text-brand">2026 Opportunity Map</p>
                <div class="mt-6 grid gap-4">
                    @foreach (['Save smarter with simple finance systems', 'Use AI agents to speed up small business work', 'Ship Laravel content and SaaS ideas with SEO foundations', 'Turn useful guides into ethical affiliate income'] as $item)
                        <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-sm font-semibold text-white/85">{{ $item }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-black uppercase text-emerald-600">Editor picks</p>
                <h2 class="mt-2 text-3xl font-black">Featured Posts</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-emerald-700">View all</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ($featuredPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
        <x-ad-slot-top label="Homepage ad placeholder" class="mt-8" />
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-black">Explore by Category</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="rounded-lg border border-black/10 p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:bg-emerald-50 hover:shadow-lg">
                        <span class="text-lg font-black">{{ $category->name }}</span>
                        <span class="mt-2 block text-sm text-slate-500">{{ $category->posts_count }} launch guides</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <p class="text-sm font-black uppercase text-emerald-600">Start Here</p>
                <h2 class="mt-2 text-3xl font-black">Choose your next move.</h2>
            </div>
            @foreach ([
                ['AI workflows', route('categories.show', 'ai'), 'Use AI tools without losing quality or trust.'],
                ['Laravel launch', route('categories.show', 'laravel'), 'Build fast, crawlable Blade-first products.'],
                ['Online income', route('categories.show', 'business'), 'Package skills and content into useful assets.'],
            ] as [$title, $url, $copy])
                <a href="{{ $url }}" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-lg">
                    <span class="text-lg font-black">{{ $title }}</span>
                    <span class="mt-3 block text-sm leading-6 text-slate-600">{{ $copy }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_360px] lg:px-8">
        <div class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
            <p class="text-sm font-black uppercase text-emerald-600">Trust</p>
            <h2 class="mt-3 text-3xl font-black">Built by Youssef Youyou, Full-Stack Developer</h2>
            <p class="mt-4 max-w-2xl leading-7 text-slate-600">This blog is built for people who want practical systems: freelancers, developers, creators, and small business builders looking for clear finance, tech, AI, and Laravel guidance.</p>
        </div>
        <x-ad-placeholder label="Homepage sponsor placeholder" />
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase text-emerald-600">Recommended Tools</p>
                    <h2 class="mt-2 text-3xl font-black">Useful tools for builders</h2>
                </div>
                <a href="{{ route('tools.index') }}" class="text-sm font-black text-emerald-700">View tools</a>
            </div>
            <x-affiliate-disclosure class="mt-6" />
            <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                @foreach ($tools as $tool)
                    <a href="{{ $tool->affiliate_url ?: route('tools.index') }}" class="rounded-lg border border-black/10 p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-lg" rel="sponsored nofollow">
                        <span class="text-xs font-black uppercase text-emerald-600">{{ $tool->category }}</span>
                        <span class="mt-2 block text-lg font-black">{{ $tool->name }}</span>
                        <span class="mt-3 block text-sm leading-6 text-slate-600">{{ $tool->description }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-black">Latest Posts</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 pb-16 sm:px-6 lg:px-8">
        <x-newsletter-card />
    </section>
</x-layouts.public>
