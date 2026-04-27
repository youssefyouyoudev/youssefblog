@php
    $popularPosts = \App\Models\Post::with('category', 'tags', 'user')->latestPublished()->orderByDesc('views')->take(5)->get();
    $categories = \App\Models\Category::withCount(['posts' => fn ($query) => $query->published()])->orderBy('name')->get();
@endphp

<aside {{ $attributes->merge(['class' => 'space-y-6']) }}>
    <x-founder-card :dark="false" />
    <div class="rounded-2xl border border-black/10 bg-white p-5 shadow-soft">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">Popular Posts</p>
        <div class="mt-4 grid gap-4">
            @foreach ($popularPosts as $post)
                <a href="{{ route('posts.show', $post) }}" class="border-b border-black/10 pb-4 last:border-b-0 last:pb-0">
                    <span class="text-xs font-black uppercase text-slate-400">{{ $post->views === 1 ? '1 view' : number_format($post->views).' views' }}</span>
                    <span class="mt-1 block text-sm font-black text-ink hover:text-emerald-700">{{ $post->title }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <div class="rounded-2xl border border-black/10 bg-white p-5 shadow-soft">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">Categories</p>
        <div class="mt-4 grid gap-2">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="flex items-center justify-between rounded-xl border border-black/10 px-3 py-2 text-sm font-bold hover:border-emerald-500 hover:bg-emerald-50">
                    <span>{{ $category->name }}</span>
                    <span>{{ $category->posts_count }}</span>
                </a>
            @endforeach
        </div>
    </div>
    <x-newsletter-box />
    <x-ad-slot />
</aside>
