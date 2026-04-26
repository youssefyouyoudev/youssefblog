<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $categoryDescriptions = [
            'finance' => 'Personal finance, freelancer money systems, fintech, savings, and online income education without risky promises.',
            'tech' => 'Hosting, tools, cybersecurity, devices, productivity systems, and the infrastructure behind serious digital work.',
            'ai' => 'AI tools, agents, ChatGPT workflows, automation, and practical systems for freelancers and businesses.',
            'laravel' => 'Laravel SEO, deployment, performance, security, SaaS ideas, dashboards, queues, and production systems.',
            'business' => 'SaaS ideas, digital services, freelancing, client acquisition, Moroccan market opportunities, and growth systems.',
        ];
    @endphp

    <section class="hero-grid bg-[#050505] text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <nav class="text-sm font-semibold text-white/55">
                <a class="hover:text-brand" href="{{ route('home') }}">Home</a>
                <span class="px-2">/</span>
                <span>{{ $category->name }}</span>
            </nav>
            <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_360px] lg:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand">Category Hub</p>
                    <h1 class="accent-line mt-4 text-4xl font-black tracking-tight sm:text-6xl">{{ $category->name }} Guides</h1>
                    <p class="mt-8 max-w-3xl text-lg leading-8 text-white/70">{{ $category->description ?: ($categoryDescriptions[$category->slug] ?? "Practical {$category->name} guides from Youssef Blog.") }}</p>
                </div>
                <x-founder-card />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Featured in {{ $category->name }}</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight">Start with these guides</h2>
            </div>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @forelse ($featuredPosts as $post)
                <x-featured-post-card :post="$post" />
            @empty
                <div class="rounded-2xl border border-black/10 bg-white p-6 text-slate-600 md:col-span-3">
                    No published {{ $category->name }} posts yet. Try Finance, AI, Laravel, Tech, or Business while this hub fills up.
                </div>
            @endforelse
        </div>
        <x-ad-slot label="Category ad placeholder" class="mt-10" />
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_340px] lg:px-8">
            <div>
                <h2 class="text-2xl font-black">Related Tags</h2>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($relatedTags as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="rounded-full border border-black/10 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            </div>
            <x-newsletter-box />
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-600">Latest Posts</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight">All {{ $category->name }} Posts</h2>
            </div>
            <a href="{{ route('services') }}" class="premium-button bg-black text-white">Work With Me</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <div class="rounded-2xl border border-black/10 bg-white p-6 text-slate-600 lg:col-span-3">
                    This category is currently empty. Explore another hub or check back after the scheduler publishes new articles.
                </div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
        <x-service-cta :title="'Need a business-ready '.$category->name.' system?'" :description="$brand['positioning']" />
    </section>
</x-layouts.public>
