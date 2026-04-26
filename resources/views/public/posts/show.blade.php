<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $blocks = collect(preg_split('/\n\s*\n/', $post->content))->filter();
        $headings = $blocks
            ->filter(fn ($block) => str_starts_with(trim($block), '## '))
            ->map(fn ($block) => trim(str_replace('## ', '', $block)))
            ->values();
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->meta_description ?: $post->excerpt,
            'image' => $post->og_image ?: $post->featured_image,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $post->user->name],
            'publisher' => ['@type' => 'Organization', 'name' => $brand['name'], 'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/brand/youssef-blog-logo.png')]],
            'mainEntityOfPage' => route('posts.show', $post),
            'keywords' => $post->keywords,
        ];
        $breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $post->category->name, 'item' => route('categories.show', $post->category)],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => route('posts.show', $post)],
            ],
        ];
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
    <script type="application/ld+json">@json($jsonLd)</script>
    <script type="application/ld+json">@json($breadcrumbLd)</script>
    @if ($faqLd)
        <script type="application/ld+json">@json($faqLd)</script>
    @endif
    <div class="fixed left-0 top-0 z-[60] h-1 w-full bg-transparent" aria-hidden="true">
        <div id="reading-progress" class="h-1 w-0 bg-brand"></div>
    </div>

    <article class="bg-white">
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <nav class="text-sm font-semibold text-slate-500">
                <a class="hover:text-emerald-700" href="{{ route('home') }}">Home</a>
                <span class="px-2">/</span>
                <a class="hover:text-emerald-700" href="{{ route('categories.show', $post->category) }}">{{ $post->category->name }}</a>
                <span class="px-2">/</span>
                <span>{{ $post->title }}</span>
            </nav>
            <a href="{{ route('categories.show', $post->category) }}" class="mt-8 inline-flex rounded-lg bg-emerald-100 px-3 py-1 text-xs font-black uppercase text-emerald-700">{{ $post->category->name }}</a>
            <h1 class="mt-5 text-4xl font-black tracking-tight sm:text-5xl">{{ $post->title }}</h1>
            <p class="mt-5 text-xl leading-8 text-slate-600">{{ $post->excerpt }}</p>
            <div class="mt-6 flex flex-wrap gap-4 text-sm font-semibold text-slate-500">
                <span>By {{ $post->user->name }}</span>
                <span>{{ $post->readingMinutes() }} min read</span>
                <span>Published {{ $post->published_at?->format('M d, Y') }}</span>
                <span>Updated {{ $post->updated_at?->format('M d, Y') }}</span>
            </div>
            <div class="mt-6 flex flex-wrap gap-2">
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('posts.show', $post)) }}" class="rounded-lg border border-black/10 px-3 py-2 text-xs font-black text-slate-700 transition hover:border-emerald-500 hover:text-emerald-700" rel="nofollow noopener" target="_blank">Share on LinkedIn</a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('posts.show', $post)) }}&text={{ urlencode($post->title) }}" class="rounded-lg border border-black/10 px-3 py-2 text-xs font-black text-slate-700 transition hover:border-emerald-500 hover:text-emerald-700" rel="nofollow noopener" target="_blank">Share on X</a>
            </div>
        </div>
        @if ($post->featured_image)
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-[16/8] w-full rounded-lg object-cover shadow-xl" width="1200" height="600" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
                @if ($post->image_credit)
                    <p class="mt-2 text-xs text-slate-500">{{ $post->image_credit }}</p>
                @endif
            </div>
        @endif
    </article>

    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[1fr_300px] lg:px-8">
        <div>
            <section class="rounded-lg border border-emerald-500/30 bg-emerald-50 p-6 shadow-sm">
                <p class="text-sm font-black uppercase text-emerald-700">Implementation CTA</p>
                <h2 class="mt-2 text-2xl font-black">Need this implemented for your business?</h2>
                <p class="mt-3 text-sm leading-6 text-slate-700">Work with Youssef Youyou on Laravel systems, SaaS MVPs, dashboards, business websites, APIs, automation layers, and AI-enabled workflows.</p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $brand['start_project_url'] }}" class="rounded-lg bg-black px-5 py-3 text-center text-sm font-black text-white">Work with Youssef</a>
                    <x-whatsapp-cta />
                </div>
            </section>
            <x-affiliate-disclosure class="mt-6" />
            <x-ad-slot-top label="Ad after intro" class="mt-6 lg:hidden" />
            @if ($headings->isNotEmpty())
                <nav class="mt-6 rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <p class="text-sm font-black uppercase text-emerald-600">Table of contents</p>
                    <div class="mt-3 grid gap-2 text-sm font-bold text-slate-700">
                        @foreach ($headings as $heading)
                            <a class="hover:text-emerald-700" href="#{{ Str::slug($heading) }}">{{ $heading }}</a>
                        @endforeach
                    </div>
                </nav>
            @endif
            <div class="content-body mt-8 rounded-lg bg-white p-6 shadow-sm sm:p-8">
                @foreach ($blocks as $block)
                    @php $trimmed = trim($block); @endphp
                    @if (str_starts_with($trimmed, '## '))
                        @php $heading = trim(str_replace('## ', '', $trimmed)); @endphp
                        <h2 id="{{ Str::slug($heading) }}">{{ $heading }}</h2>
                    @elseif (str_starts_with($trimmed, '### '))
                        <h3>{{ trim(str_replace('### ', '', $trimmed)) }}</h3>
                    @else
                        <p>{!! nl2br(e($trimmed)) !!}</p>
                    @endif

                    @if (in_array($loop->iteration, [3, 8], true))
                        <x-ad-slot-middle label="{{ $loop->iteration === 3 ? 'Ad after paragraph 3' : 'Ad after paragraph 8' }}" class="my-8" />
                    @elseif ($loop->iteration === max(4, (int) floor($blocks->count() / 2)))
                        <x-ad-slot-middle label="Mobile mid article ad" class="my-8 lg:hidden" />
                    @endif
                @endforeach
            </div>
            @if ($post->faqs)
                <section class="mt-8 rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black">FAQ</h2>
                    <div class="mt-5 grid gap-4">
                        @foreach ($post->faqs as $faq)
                            <details class="rounded-lg border border-black/10 p-4">
                                <summary class="cursor-pointer font-black">{{ $faq['question'] }}</summary>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $faq['answer'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endif
            <section class="mt-8 rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                <h2 class="text-2xl font-black">Continue Reading</h2>
                <div class="mt-4 grid gap-3">
                    @foreach ($internalLinks as $link)
                        <a href="{{ route('posts.show', $link) }}" class="text-sm font-bold text-emerald-700 hover:text-black">{{ $link->title }}</a>
                    @endforeach
                </div>
            </section>
            <x-author-trust-box class="mt-8" />
            <div class="mt-8 flex flex-wrap gap-2">
                @foreach ($post->tags as $tag)
                    <a href="{{ route('tags.show', $tag) }}" class="rounded-lg border border-black/10 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-emerald-500 hover:text-emerald-700">#{{ $tag->name }}</a>
                @endforeach
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @if ($previousPost)
                    <a href="{{ route('posts.show', $previousPost) }}" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                        <span class="text-xs font-black uppercase text-slate-500">Previous</span>
                        <span class="mt-2 block font-black">{{ $previousPost->title }}</span>
                    </a>
                @endif
                @if ($nextPost)
                    <a href="{{ route('posts.show', $nextPost) }}" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                        <span class="text-xs font-black uppercase text-slate-500">Next</span>
                        <span class="mt-2 block font-black">{{ $nextPost->title }}</span>
                    </a>
                @endif
            </div>
            <x-ad-slot-bottom label="Ad before related posts" class="mt-8" />
            <x-related-posts :posts="$relatedPosts" />
            <x-hire-youssef-banner class="mt-10" />
        </div>
        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            <x-ad-slot-top label="Sticky sidebar ad" />
            <x-service-cta-card title="Laravel Development" :description="$brand['services']['Laravel Development']" />
            <x-service-cta-card title="SaaS MVP" :description="$brand['services']['SaaS Platforms']" />
            <x-service-cta-card title="Business Website" :description="$brand['services']['Business Websites']" />
            <x-service-cta-card title="CRM / ERP Dashboard" :description="$brand['services']['CRM / ERP Systems']" />
            <x-newsletter-card />
        </aside>
    </div>
    <script>
        document.addEventListener('scroll', () => {
            const height = document.documentElement.scrollHeight - window.innerHeight;
            const progress = height > 0 ? (window.scrollY / height) * 100 : 0;
            document.getElementById('reading-progress').style.width = `${progress}%`;
        }, { passive: true });
    </script>
</x-layouts.public>
