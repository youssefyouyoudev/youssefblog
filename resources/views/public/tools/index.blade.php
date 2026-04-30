<x-layouts.public :seo="$seo">
    <section class="border-b border-[var(--border)] bg-[var(--surface)]">
        <div class="safe-container py-12 lg:py-14">
            <p class="text-sm font-black uppercase tracking-wide text-[var(--accent)]">Recommended Tools</p>
            <h1 class="mt-3 text-[clamp(2rem,9vw,3.25rem)] font-black leading-tight text-[var(--text)]">Tools for building smarter in 2026</h1>
            <p class="mt-4 max-w-3xl text-base leading-8 text-[var(--muted)] sm:text-lg">A curated list of hosting, AI, developer, and finance tools that fit the Youssef Youyou Blog audience. Some links may become affiliate links and will be labeled clearly.</p>
            <x-affiliate-disclosure class="mt-6" />
        </div>
    </section>

    <section class="safe-container py-12">
        <div class="grid gap-10">
            @foreach ($toolsByCategory as $category => $tools)
                <div>
                    <h2 class="text-2xl font-black text-[var(--text)]">{{ $category }}</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($tools as $tool)
                            <article class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow sm:p-6">
                                <p class="text-xs font-black uppercase text-[var(--accent)]">{{ $tool->category }}</p>
                                <h3 class="mt-2 text-xl font-black text-[var(--text)]">{{ $tool->name }}</h3>
                                <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $tool->description }}</p>
                                @if ($tool->affiliate_url)
                                    <a href="{{ $tool->affiliate_url }}" rel="sponsored nofollow" class="premium-button mt-5 bg-[var(--text)] text-[var(--bg)]">Visit tool</a>
                                @else
                                    <span class="mt-5 inline-flex min-h-11 items-center rounded-full bg-[var(--surface-2)] px-4 py-2 text-sm font-black text-[var(--muted)]">No affiliate link</span>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.public>
