<x-layouts.public :seo="$seo">
    @php($brand = config('brand'))

    <section class="overflow-hidden bg-white">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-20">
            <div>
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-28 w-auto rounded-xl object-contain shadow-sm sm:h-36" width="320" height="144">
                <p class="mt-8 text-sm font-black uppercase tracking-wide text-emerald-600">{{ $brand['insights'] }}</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-tight text-black sm:text-6xl">Smart Finance, Tech & AI Insights for Builders and Business Owners</h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Practical guides from Youssef Youyou on AI tools, Laravel, SaaS, online business, and digital systems that help businesses grow.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('posts.index') }}" class="rounded-lg bg-black px-6 py-3 text-center text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-900">Read Latest Posts</a>
                    <a href="{{ $brand['start_project_url'] }}" class="rounded-lg border border-black/10 bg-brand px-6 py-3 text-center text-sm font-black text-black shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-300">Start a Project</a>
                    <a href="{{ $brand['portfolio_url'] }}" class="rounded-lg border border-black/10 bg-white px-6 py-3 text-center text-sm font-black text-black shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-500">View Portfolio</a>
                </div>
            </div>
            <div class="rounded-lg border border-black/10 bg-black p-6 text-white shadow-2xl">
                <p class="text-sm font-black uppercase tracking-wide text-brand">Premium full-stack delivery</p>
                <h2 class="mt-3 text-3xl font-black">Websites, SaaS platforms, dashboards, and custom software built to win trust fast.</h2>
                <div class="mt-6 grid gap-4">
                    @foreach (['Business websites and landing pages', 'SaaS MVPs and product dashboards', 'CRM / ERP systems and internal tools', 'API, automation, AI-enabled workflows, deployment'] as $item)
                        <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-sm font-semibold text-white/85">{{ $item }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-black/10 bg-black text-white">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 py-6 sm:px-6 md:grid-cols-4 lg:px-8">
            @foreach ($brand['stats'] as $stat)
                <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-sm font-black">{{ $stat }}</div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-black uppercase text-emerald-600">Featured Guides</p>
                <h2 class="mt-2 text-3xl font-black">Proof of technical and business thinking</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-emerald-700">View all</a>
        </div>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ($featuredPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-black">Category Hubs</h2>
            <p class="mt-3 max-w-2xl text-slate-600">Finance, tech, AI, Laravel, and business content connected to real software delivery and digital growth.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="rounded-lg border border-black/10 p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:bg-emerald-50 hover:shadow-lg">
                        <span class="text-lg font-black">{{ $category->name }}</span>
                        <span class="mt-2 block text-sm text-slate-500">{{ $category->posts_count }} guides</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <x-hire-youssef-banner />
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <x-portfolio-link-card title="View flagship case studies" description="See eCarsAuto, Rifi Media TV, WaslaCRM, ERP Plus, Invoix, and other product directions framed like real business assets." />
            <x-service-cta-card title="Business Websites" :description="$brand['services']['Business Websites']" />
            <x-service-cta-card title="SaaS Platforms" :description="$brand['services']['SaaS Platforms']" />
        </div>
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase text-emerald-600">Recommended Tools</p>
                    <h2 class="mt-2 text-3xl font-black">Tools that support serious builds</h2>
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
