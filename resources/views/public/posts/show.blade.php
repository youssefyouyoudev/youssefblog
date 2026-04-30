<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $blocks = collect(preg_split('/\n\s*\n/', $post->content))->filter();
        $headings = $blocks
            ->filter(fn ($block) => str_starts_with(trim($block), '## '))
            ->map(fn ($block) => trim(str_replace('## ', '', $block)))
            ->values();
        $seenHeadings = [];
        $articleHtml = $blocks->map(function ($block) use (&$seenHeadings): string {
            $trimmed = trim($block);

            if (str_starts_with($trimmed, '```')) {
                return '<pre><code>'.e(trim($trimmed, "` \n\r\t")).'</code></pre>';
            }

            if (str_starts_with($trimmed, '## ')) {
                $heading = trim(str_replace('## ', '', $trimmed));
                $key = Str::slug($heading);

                if (isset($seenHeadings[$key])) {
                    return '<p class="font-black">'.e($heading).'</p>';
                }

                $seenHeadings[$key] = true;

                return '<h2 id="'.Str::slug($heading).'">'.e($heading).'</h2>';
            }

            if (str_starts_with($trimmed, '### ')) {
                $heading = trim(str_replace('### ', '', $trimmed));
                $key = Str::slug($heading);

                if (isset($seenHeadings[$key])) {
                    return '<p class="font-black">'.e($heading).'</p>';
                }

                $seenHeadings[$key] = true;

                return '<h3 id="'.Str::slug($heading).'">'.e($heading).'</h3>';
            }

            return '<p>'.nl2br(e($trimmed)).'</p>';
        })->implode("\n");
        $faqLd = $post->faqs ? [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($post->faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
            ])->values(),
        ] : null;
        $featuredImage = $post->featured_image ?: asset('assets/brand/youssef-blog-og.png');
        $updatedAt = $post->last_updated_at ?: $post->updated_at;
    @endphp

    @if ($faqLd)
        <script type="application/ld+json">@json($faqLd)</script>
    @endif

    <x-article-progress />

    <article>
        <section class="hero-grid text-white">
            <div class="safe-container py-10 lg:py-12">
                <nav class="flex flex-wrap gap-y-1 text-sm font-semibold text-white/55">
                    <a class="hover:text-emerald-300" href="{{ route('home') }}">Home</a>
                    <span class="px-2">/</span>
                    <a class="hover:text-emerald-300" href="{{ route('categories.show', $post->category) }}">{{ $post->category->name }}</a>
                    <span class="px-2">/</span>
                    <span>{{ Str::limit($post->title, 54) }}</span>
                </nav>
                <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_340px] lg:items-end">
                    <div>
                        <a href="{{ route('categories.show', $post->category) }}" class="category-pill">{{ $post->category->name }}</a>
                        <h1 class="mt-5 max-w-4xl text-[clamp(1.875rem,9vw,3.5rem)] font-black leading-tight tracking-tight">{{ $post->title }}</h1>
                        <p class="mt-5 max-w-3xl text-lg leading-8 text-white/70 sm:text-xl">{{ $post->excerpt }}</p>
                        <div class="mt-7 flex flex-wrap gap-3 text-sm font-bold text-white/60">
                            <span>By {{ $post->user->name ?: 'Youssef Youyou' }}</span>
                            <span>{{ $post->readingMinutes() }} min read</span>
                            <time datetime="{{ $post->published_at?->toDateString() }}">Published {{ $post->published_at?->format('M d, Y') }}</time>
                            <time datetime="{{ $updatedAt?->toDateString() }}">Updated {{ $updatedAt?->format('M d, Y') }}</time>
                        </div>
                        <x-social-share :url="route('posts.show', $post)" :title="$post->title" class="mt-6 text-white/75" />
                    </div>
                    <x-founder-card />
                </div>
            </div>
        </section>

        <div class="safe-container max-w-6xl py-6 sm:py-8">
            <img src="{{ $featuredImage }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-[16/10] w-full rounded-2xl border border-[var(--border)] object-cover shadow-glow sm:aspect-[16/8] sm:rounded-3xl" width="1200" height="600" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
            @if ($post->image_credit)
                <p class="mt-3 text-xs font-semibold text-[var(--muted)]">{{ $post->image_credit }}</p>
            @endif
        </div>

        <div class="safe-container grid gap-8 py-6 sm:py-8 lg:grid-cols-[240px_minmax(0,800px)_280px] xl:grid-cols-[260px_minmax(0,800px)_300px]">
            <aside class="hidden lg:block">
                <div class="sticky top-24 space-y-6">
                    <x-table-of-contents :headings="$headings" />
                    <x-ad-slot />
                </div>
            </aside>

            <div class="min-w-0">
                <x-author-trust-box compact />

                <div class="mt-6 lg:hidden">
                    <x-table-of-contents :headings="$headings" collapsible />
                </div>

                <x-editorial-note :post="$post" class="mt-6" />

                <div class="content-body mt-8 rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft sm:rounded-3xl sm:p-8 lg:p-10">
                    {!! \App\Helpers\ContentHelper::process($articleHtml) !!}
                </div>

                <section class="mt-8">
                    <x-service-cta title="Need help building a Laravel/SaaS project?" description="Work with Youssef Youyou on a focused, production-ready build: UX, Laravel backend, integrations, deployment, and launch polish." />
                </section>

                @if ($post->faqs)
                    <section class="mt-8 rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-soft sm:p-8">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">FAQ</p>
                        <h2 class="mt-3 text-3xl font-black text-[var(--text)]">Common questions</h2>
                        <div class="mt-6 grid gap-4">
                            @foreach ($post->faqs as $faq)
                                <details class="rounded-2xl border border-[var(--border)] p-5 transition hover:border-[var(--accent)] hover:bg-[var(--accent-soft)]">
                                    <summary class="cursor-pointer font-black text-[var(--text)]">{{ $faq['question'] }}</summary>
                                    <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $faq['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($internalLinks->isNotEmpty())
                    <section class="mt-8 rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-soft sm:p-8">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">Read Next</p>
                        <h2 class="mt-3 text-2xl font-black text-[var(--text)]">Internal links worth opening</h2>
                        <div class="mt-5 grid gap-3">
                            @foreach ($internalLinks as $link)
                                <a href="{{ route('posts.show', $link) }}" class="rounded-xl border border-[var(--border)] px-4 py-3 text-sm font-black text-[var(--text)] transition hover:border-[var(--accent)] hover:bg-[var(--accent-soft)] hover:text-[var(--accent)]">{{ $link->shortAnchorTitle() }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <x-author-trust-box class="mt-8" />

                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="rounded-full border border-[var(--border)] bg-[var(--surface)] px-4 py-2 text-sm font-bold text-[var(--muted)] transition hover:border-[var(--accent)] hover:bg-[var(--accent-soft)] hover:text-[var(--accent)]">#{{ $tag->name }}</a>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @if ($previousPost)
                        <a href="{{ route('posts.show', $previousPost) }}" class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)]">
                            <span class="text-xs font-black uppercase text-[var(--muted)]">Previous</span>
                            <span class="mt-2 block font-black text-[var(--text)]">{{ $previousPost->title }}</span>
                        </a>
                    @endif
                    @if ($nextPost)
                        <a href="{{ route('posts.show', $nextPost) }}" class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)]">
                            <span class="text-xs font-black uppercase text-[var(--muted)]">Next</span>
                            <span class="mt-2 block font-black text-[var(--text)]">{{ $nextPost->title }}</span>
                        </a>
                    @endif
                </div>

                <x-related-posts :posts="$relatedPosts" />
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <x-blog-sidebar />
            </aside>
        </div>
    </article>
</x-layouts.public>
