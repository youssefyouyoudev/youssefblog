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

                return '<h3>'.e($heading).'</h3>';
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
    @endphp
    @if ($faqLd)
        <script type="application/ld+json">@json($faqLd)</script>
    @endif
    <x-article-progress />

    <article>
        <section class="hero-grid bg-[#050505] text-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <nav class="text-sm font-semibold text-white/55">
                    <a class="hover:text-brand" href="{{ route('home') }}">Home</a>
                    <span class="px-2">/</span>
                    <a class="hover:text-brand" href="{{ route('categories.show', $post->category) }}">{{ $post->category->name }}</a>
                    <span class="px-2">/</span>
                    <span>{{ $post->title }}</span>
                </nav>
                <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_340px] lg:items-end">
                    <div>
                        <a href="{{ route('categories.show', $post->category) }}" class="category-pill">{{ $post->category->name }}</a>
                        <h1 class="mt-5 max-w-4xl text-4xl font-black tracking-tight sm:text-6xl">{{ $post->title }}</h1>
                        <p class="mt-5 max-w-3xl text-xl leading-8 text-white/70">{{ $post->excerpt }}</p>
                        <div class="mt-7 flex flex-wrap gap-3 text-sm font-bold text-white/60">
                            <span>Written by {{ $post->user->name }}</span>
                            <span>{{ $post->readingMinutes() }} min read</span>
                            <span>Published {{ $post->published_at?->format('M d, Y') }}</span>
                            <span>Last updated {{ $post->updated_at?->format('M d, Y') }}</span>
                        </div>
                        <div class="mt-6 flex flex-wrap gap-2">
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('posts.show', $post)) }}" class="premium-button border border-white/20 text-white hover:border-brand hover:text-brand" rel="nofollow noopener" target="_blank">Share LinkedIn</a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('posts.show', $post)) }}&text={{ urlencode($post->title) }}" class="premium-button border border-white/20 text-white hover:border-brand hover:text-brand" rel="nofollow noopener" target="_blank">Share X</a>
                            <a href="https://wa.me/?text={{ urlencode($post->title.' '.route('posts.show', $post)) }}" class="premium-button border border-white/20 text-white hover:border-brand hover:text-brand" rel="nofollow noopener" target="_blank">Share WhatsApp</a>
                        </div>
                    </div>
                    <x-founder-card />
                </div>
            </div>
        </section>

        @if ($post->featured_image)
            <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-[16/8] w-full rounded-3xl object-cover shadow-glow" width="1200" height="600" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
                @if ($post->image_credit)
                    <p class="mt-3 text-xs font-semibold text-slate-500">{{ $post->image_credit }}</p>
                @endif
            </div>
        @endif

        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-8 sm:px-6 lg:grid-cols-[260px_minmax(0,720px)_300px] lg:px-8">
            <aside class="hidden lg:block">
                <div class="sticky top-8 space-y-6">
                    <x-table-of-contents :content="$post->content" />
                    <x-ad-slot />
                </div>
            </aside>

            <div class="min-w-0">
                <x-author-trust-box compact />
                <x-editorial-note :post="$post" class="mt-6" />

                <x-ad-slot class="mt-8" />

                <div class="content-body mt-8 rounded-3xl border border-black/10 bg-white p-6 shadow-soft sm:p-10">
                    {!! \App\Helpers\ContentHelper::process($articleHtml) !!}
                </div>

                @if ($post->faqs)
                    <section class="mt-8 rounded-3xl border border-black/10 bg-white p-6 shadow-soft sm:p-8">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">FAQ</p>
                        <h2 class="mt-3 text-3xl font-black">Common questions</h2>
                        <div class="mt-6 grid gap-4">
                            @foreach ($post->faqs as $faq)
                                <details class="rounded-2xl border border-black/10 p-5 transition hover:border-emerald-500 hover:bg-emerald-50">
                                    <summary class="cursor-pointer font-black">{{ $faq['question'] }}</summary>
                                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $faq['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="mt-8 rounded-3xl border border-black/10 bg-white p-6 shadow-soft sm:p-8">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">Read Next</p>
                    <h2 class="mt-3 text-2xl font-black">Internal links worth opening</h2>
                    <div class="mt-5 grid gap-3">
                        @foreach ($internalLinks as $link)
                            <a href="{{ route('posts.show', $link) }}" class="rounded-xl border border-black/10 px-4 py-3 text-sm font-black text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-black">{{ Str::limit($link->title, 72) }}</a>
                        @endforeach
                    </div>
                </section>

                <x-author-trust-box class="mt-8 rounded-3xl shadow-glow" />

                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="rounded-full border border-black/10 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:border-emerald-500 hover:bg-emerald-50 hover:text-emerald-700">#{{ $tag->name }}</a>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @if ($previousPost)
                        <a href="{{ route('posts.show', $previousPost) }}" class="rounded-2xl border border-black/10 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-emerald-500">
                            <span class="text-xs font-black uppercase text-slate-500">Previous</span>
                            <span class="mt-2 block font-black">{{ $previousPost->title }}</span>
                        </a>
                    @endif
                    @if ($nextPost)
                        <a href="{{ route('posts.show', $nextPost) }}" class="rounded-2xl border border-black/10 bg-white p-5 shadow-soft transition hover:-translate-y-1 hover:border-emerald-500">
                            <span class="text-xs font-black uppercase text-slate-500">Next</span>
                            <span class="mt-2 block font-black">{{ $nextPost->title }}</span>
                        </a>
                    @endif
                </div>

                <x-ad-slot class="mt-8" />
                <x-related-posts :posts="$relatedPosts" />
                <x-newsletter-box class="mt-10" />
                <x-service-cta class="mt-10" />
            </div>

            <aside class="space-y-6 lg:sticky lg:top-8 lg:self-start">
                <x-blog-sidebar />
            </aside>
        </div>
        <div class="fixed inset-x-4 bottom-4 z-50 flex justify-center gap-2 rounded-2xl border border-black/10 bg-white/95 p-2 shadow-2xl backdrop-blur lg:hidden">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('posts.show', $post)) }}&text={{ urlencode($post->title) }}" class="flex-1 rounded-xl bg-black px-3 py-2 text-center text-xs font-black text-white" rel="nofollow noopener" target="_blank">X</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('posts.show', $post)) }}" class="flex-1 rounded-xl bg-black px-3 py-2 text-center text-xs font-black text-white" rel="nofollow noopener" target="_blank">LinkedIn</a>
            <a href="https://wa.me/?text={{ urlencode($post->title.' '.route('posts.show', $post)) }}" class="flex-1 rounded-xl bg-brand px-3 py-2 text-center text-xs font-black text-black" rel="nofollow noopener" target="_blank">WhatsApp</a>
        </div>
    </article>
</x-layouts.public>
