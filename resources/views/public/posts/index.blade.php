<x-layouts.public :seo="$seo">
    @php
        $categories = \App\Models\Category::withCount(['posts' => fn ($query) => $query->published()])
            ->orderByDesc('posts_count')
            ->get();
        $search = request('q');
    @endphp

    <section class="border-b border-[var(--border)] bg-[var(--surface)]">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <nav class="text-sm font-bold text-[var(--muted)]">
                <a class="hover:text-[var(--accent)]" href="{{ route('home') }}">Home</a>
                <span class="px-2">/</span>
                <span>{{ $heading ?? 'Articles' }}</span>
            </nav>
            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px] lg:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-[var(--accent)]">Youssef Youyou Blog</p>
                    <h1 class="mt-3 text-4xl font-black tracking-tight text-[var(--text)] sm:text-5xl">{{ $heading ?? 'Latest Articles' }}</h1>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-[var(--muted)]">{{ $description ?? 'Browse practical Laravel, SaaS, AI, finance systems, and digital business guides.' }}</p>
                </div>
                <form method="GET" action="{{ route('posts.index') }}" class="rounded-2xl border border-[var(--border)] bg-[var(--bg)] p-4 shadow-soft">
                    <label for="q" class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">Search guides</label>
                    <div class="mt-3 flex gap-2">
                        <input id="q" name="q" value="{{ $search }}" type="search" placeholder="Laravel SEO, AI tools..." class="min-h-12 min-w-0 flex-1 border border-[var(--border)] bg-[var(--surface)] px-4 text-sm text-[var(--text)] focus:border-[var(--accent)]">
                        <button class="premium-button bg-[var(--accent)] text-white" type="submit">Search</button>
                    </div>
                </form>
            </div>
            <div class="mt-8 flex gap-2 overflow-x-auto pb-2">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="shrink-0 rounded-full border border-[var(--border)] bg-[var(--bg)] px-4 py-2 text-sm font-bold text-[var(--muted)] hover:border-[var(--accent)] hover:text-[var(--accent)]">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <div class="rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-8 text-[var(--muted)] shadow-soft lg:col-span-3">
                    <p class="text-xl font-black text-[var(--text)]">No matching guides yet.</p>
                    <p class="mt-2 text-sm leading-6">Try a broader search or browse the category chips above.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->withQueryString()->links() }}</div>
    </section>
</x-layouts.public>
