<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $trustChips = ['Laravel', 'SaaS', 'AI Tools', 'Moroccan SMEs', 'Freelancing'];
        $categoryIcons = [
            'laravel' => '{}',
            'ai' => 'AI',
            'finance' => '$',
            'tech' => '</>',
            'business' => 'B2B',
        ];
    @endphp

    <section class="hero-grid overflow-hidden text-white">
        <div class="safe-container grid items-center gap-10 py-12 sm:py-16 lg:grid-cols-[1.02fr_.98fr] lg:py-24">
            <div class="fade-up">
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-300">Youssef Youyou Blog</p>
                <h1 class="accent-line mt-5 max-w-5xl text-[clamp(2rem,11vw,3rem)] font-black leading-[1.04] tracking-tight sm:text-6xl lg:text-7xl">Practical Laravel, SaaS, AI & Business Guides</h1>
                <p class="mt-8 max-w-2xl text-lg leading-8 text-white/70">Real-world guides for developers, freelancers, and Moroccan businesses building better digital systems.</p>
                <div class="mt-8 flex flex-col gap-3 min-[430px]:flex-row min-[430px]:flex-wrap">
                    <a href="{{ route('posts.index') }}" class="premium-button bg-emerald-600 text-white">Read Latest Articles</a>
                    <a href="{{ $brand['portfolio_url'] }}" class="premium-button border border-white/20 text-white hover:border-emerald-300 hover:text-emerald-200" rel="noopener noreferrer">Work With Youssef</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($trustChips as $chip)
                        <span class="rounded-full border border-white/10 bg-white/10 px-4 py-2 text-xs font-black text-white/80 backdrop-blur">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-5">
                @if ($featuredPosts->isNotEmpty())
                    <x-featured-post-card :post="$featuredPosts->first()" large />
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['Production Laravel thinking', 'Practical AI workflows', 'Finance systems mindset', 'Morocco + global markets'] as $item)
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-sm font-black text-white/80 shadow-soft backdrop-blur">{{ $item }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-[var(--border)] bg-[var(--surface)]">
        <div class="safe-container grid gap-4 py-5 text-sm font-black text-[var(--muted)] sm:grid-cols-2 lg:grid-cols-4">
            <span>Built by a Laravel developer</span>
            <span>Readable, practical guides</span>
            <span>Business-first systems thinking</span>
            <span>Transparent editorial approach</span>
        </div>
    </section>

    <section class="safe-container py-12 lg:py-14">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-[var(--accent)]">Featured Articles</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-[var(--text)] sm:text-4xl">Editor-picked guides</h2>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-[var(--muted)]">Start with practical pieces that connect development decisions to real business outcomes.</p>
            </div>
            <a href="{{ route('posts.index') }}" class="premium-button border border-[var(--border)] bg-[var(--surface)] text-[var(--text)]">All Articles</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($featuredPosts as $post)
                <x-featured-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="border-y border-[var(--border)] bg-[var(--surface)]">
        <div class="safe-container py-12 lg:py-14">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-[var(--accent)]">Browse by Intent</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight text-[var(--text)] sm:text-4xl">Guides for focused work</h2>
                </div>
            </div>
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($guideGroups as [$groupTitle, $groupDescription, $posts])
                    <article class="rounded-2xl border border-[var(--border)] bg-[var(--bg)] p-6 shadow-soft">
                        <h3 class="text-xl font-black text-[var(--text)]">{{ $groupTitle }}</h3>
                        <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $groupDescription }}</p>
                        <div class="mt-5 grid gap-3">
                            @foreach ($posts as $post)
                                <a href="{{ route('posts.show', $post) }}" class="rounded-xl border border-[var(--border)] bg-[var(--surface)] px-4 py-3 text-sm font-black leading-snug text-[var(--text)] transition hover:border-[var(--accent)] hover:bg-[var(--accent-soft)] hover:text-[var(--accent)]">{{ $post->title }}</a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section id="categories" class="safe-container py-12 lg:py-14">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.24em] text-[var(--accent)]">Categories</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-[var(--text)] sm:text-4xl">Explore the knowledge base</h2>
        </div>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="group rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)]">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--accent-soft)] text-sm font-black text-[var(--accent)]">{{ $categoryIcons[$category->slug] ?? 'YY' }}</span>
                    <span class="mt-5 block text-lg font-black text-[var(--text)] group-hover:text-[var(--accent)]">{{ $category->name }}</span>
                    <span class="mt-2 block text-sm text-[var(--muted)]">{{ $category->posts_count }} {{ Str::plural('guide', $category->posts_count) }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="safe-container py-12 lg:py-14">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-[var(--accent)]">Latest Articles</p>
                <h2 class="mt-3 text-3xl font-black tracking-tight text-[var(--text)] sm:text-4xl">Fresh from the blog</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="text-sm font-black text-[var(--accent)]">View archive</a>
        </div>
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>

    <section class="safe-container grid gap-6 py-12 lg:grid-cols-[.95fr_1.05fr] lg:py-14">
        <x-newsletter-box />
        <x-founder-card :dark="false" />
    </section>

    <section class="safe-container pb-14 lg:pb-16">
        <x-service-cta title="Need help building a Laravel/SaaS project?" description="Work with Youssef on a focused build that connects UX, Laravel architecture, automation, deployment, and business goals." />
    </section>
</x-layouts.public>
