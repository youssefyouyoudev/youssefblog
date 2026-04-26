<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $reviewLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Review',
            'name' => $page['title'],
            'itemReviewed' => [
                '@type' => 'Product',
                'name' => $page['title'],
                'category' => $page['category'],
            ],
            'reviewBody' => $page['excerpt'].' This comparison explains practical fit, strengths, and trade-offs without claiming guaranteed results.',
            'author' => ['@type' => 'Person', 'name' => $brand['name']],
            'publisher' => ['@type' => 'Organization', 'name' => $brand['name']],
        ];
        $breadcrumbLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Best', 'item' => route('money.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $page['title'], 'item' => route('money.show', $page['slug'])],
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($reviewLd)</script>
    <script type="application/ld+json">@json($breadcrumbLd)</script>

    <article class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_320px] lg:px-8">
            <div>
                <nav class="text-sm font-semibold text-slate-500">
                    <a class="hover:text-emerald-700" href="{{ route('home') }}">Home</a>
                    <span class="px-2">/</span>
                    <a class="hover:text-emerald-700" href="{{ route('money.index') }}">Best</a>
                    <span class="px-2">/</span>
                    <span>{{ $page['title'] }}</span>
                </nav>

                <span class="mt-8 inline-flex rounded-lg bg-emerald-100 px-3 py-1 text-xs font-black uppercase text-emerald-700">{{ $page['category'] }}</span>
                <h1 class="mt-5 text-4xl font-black tracking-tight sm:text-6xl">{{ $page['title'] }}</h1>
                <p class="mt-5 max-w-3xl text-xl leading-8 text-slate-600">{{ $page['excerpt'] }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach ($page['keywords'] as $keyword)
                        <span class="rounded-lg border border-black/10 px-3 py-2 text-xs font-black text-slate-600">{{ $keyword }}</span>
                    @endforeach
                </div>

                <img src="{{ $page['image'] }}" alt="{{ $page['title'] }}" class="mt-8 aspect-[16/8] w-full rounded-lg object-cover shadow-xl" width="1200" height="600" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">

                <x-affiliate-disclosure class="mt-8" />

                <section class="content-body mt-8 rounded-lg bg-white p-6 shadow-sm sm:p-8">
                    <h2 id="quick-verdict">Quick verdict</h2>
                    <p>This guide is built for readers who want a practical shortlist, not a noisy ranking. The best choice depends on your budget, technical comfort, support needs, and whether the tool will support a real business workflow.</p>
                    <h2 id="comparison-table">Comparison table</h2>
                    <x-comparison-table :rows="$page['rows']" />
                    <h2 id="how-to-choose">How to choose in 2026</h2>
                    <p>Start with the job to be done. A freelancer testing a small offer needs different tooling than a SaaS founder deploying Laravel queues, backups, and background jobs. Write down your required features, monthly budget, risk tolerance, and support expectations before comparing prices.</p>
                    <h3>Buying checklist</h3>
                    <p>Check reliability, support, renewal pricing, cancellation terms, data portability, privacy, security basics, and whether the tool can grow with you for the next twelve months. Avoid buying the biggest plan before the project has traction.</p>
                    <h2 id="recommended-next-step">Recommended next step</h2>
                    <p>If this choice is connected to a website, SaaS MVP, dashboard, Laravel app, or automation workflow, the smartest move is to design the business system first, then pick the tool stack around that system.</p>
                </section>

                <section class="mt-8 rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    <h2 class="text-2xl font-black">Read next</h2>
                    <div class="mt-4 grid gap-3">
                        @foreach ($relatedPages as $relatedPage)
                            <a href="{{ route('money.show', $relatedPage['slug']) }}" class="font-bold text-emerald-700 hover:text-black">{{ $relatedPage['title'] }}</a>
                        @endforeach
                    </div>
                </section>

                <x-hire-youssef-banner class="mt-8" />
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <x-ad-slot-top label="Sidebar ad slot" />
                <x-service-cta-card title="Need implementation?" description="Work with Youssef on the website, SaaS platform, dashboard, API, or automation stack behind your next business move." />
                <x-newsletter-card />
            </aside>
        </div>
    </article>
</x-layouts.public>
