<x-layouts.public :seo="$seo">
    <section class="hero-grid text-white">
        <div class="safe-container grid gap-8 py-12 lg:grid-cols-[1fr_360px] lg:items-end lg:py-16">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-300">Author</p>
                <h1 class="accent-line mt-4 text-[clamp(2rem,10vw,3.75rem)] font-black leading-tight tracking-tight">Youssef Youyou</h1>
                <p class="mt-8 max-w-3xl text-lg leading-8 text-white/70">Full-stack Laravel developer in Morocco writing practical guides about web development, AI workflows, finance habits, SaaS, SEO, and digital business systems.</p>
            </div>
            <x-founder-card />
        </div>
    </section>

    <section class="safe-container grid gap-8 py-12 lg:grid-cols-[.85fr_1.15fr]">
        <div class="rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-soft sm:p-8">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">Editorial lens</p>
            <div class="content-body mt-5">
                <p>I write from the perspective of someone who builds and maintains real Laravel websites, dashboards, SaaS foundations, APIs, and business workflows. The blog focuses on useful explanations, practical tradeoffs, and beginner-friendly steps.</p>
                <p>Readers should expect honest notes, not exaggerated guarantees. Finance articles are educational, AI articles include human review warnings, and Laravel articles favor maintainable production habits.</p>
            </div>
            <a href="{{ route('contact') }}" class="premium-button mt-5 bg-[var(--accent)] text-white">Contact Youssef</a>
        </div>

        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">Latest writing</p>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                @foreach ($latestPosts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)]">
                        <span class="category-pill">{{ $post->category->name }}</span>
                        <span class="mt-4 block font-black leading-snug text-[var(--text)]">{{ $post->title }}</span>
                        <span class="mt-3 block text-xs font-bold text-[var(--muted)]">{{ $post->published_at?->format('M d, Y') }} · {{ $post->readingMinutes() }} min read</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.public>
