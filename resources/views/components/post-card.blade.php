@props(['post'])

<article class="group overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
    @if ($post->featured_image)
        <a href="{{ route('posts.show', $post) }}" class="block aspect-[16/9] overflow-hidden bg-slate-100">
            <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" width="640" height="360" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
        </a>
    @endif
    <div class="p-5">
        <a href="{{ route('categories.show', $post->category) }}" class="text-xs font-black uppercase tracking-wide text-emerald-600">{{ $post->category->name }}</a>
        <h2 class="mt-3 text-xl font-black leading-tight">
            <a href="{{ route('posts.show', $post) }}" class="hover:text-emerald-700">{{ $post->title }}</a>
        </h2>
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $post->excerpt }}</p>
        <div class="mt-5 flex items-center justify-between text-xs font-semibold text-slate-500">
            <span>{{ $post->published_at?->format('M d, Y') }}</span>
            <span>{{ $post->readingMinutes() }} min read</span>
        </div>
    </div>
</article>
