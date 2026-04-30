<x-layouts.public :seo="$seo">
    <section class="hero-grid text-white">
        <div class="safe-container py-12 lg:py-14">
            <p class="text-sm font-black uppercase tracking-wide text-emerald-300">Affiliate Money Pages</p>
            <h1 class="mt-4 max-w-4xl text-[clamp(2rem,9vw,3.75rem)] font-black leading-tight tracking-tight">Best tools, hosting, devices, and finance comparisons for builders.</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-white/70">Practical comparison guides for Morocco and global readers, written to help you choose tools without hype or fake promises.</p>
        </div>
    </section>

    <section class="safe-container py-12">
        <x-affiliate-disclosure />
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($moneyPages as $page)
                <a href="{{ route('money.show', $page['slug']) }}" class="group overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow">
                    <img src="{{ $page['image'] }}" alt="{{ $page['title'] }}" class="aspect-[16/9] w-full object-cover" width="700" height="394" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
                    <div class="p-5">
                        <span class="text-xs font-black uppercase text-[var(--accent)]">{{ $page['category'] }}</span>
                        <h2 class="mt-2 text-xl font-black text-[var(--text)] group-hover:text-[var(--accent)]">{{ $page['title'] }}</h2>
                        <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $page['excerpt'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="safe-container pb-14">
        <x-hire-youssef-banner />
    </section>
</x-layouts.public>
