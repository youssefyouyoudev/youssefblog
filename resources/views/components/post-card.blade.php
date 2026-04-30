@props(['post'])

@php($image = $post->featured_image ?: asset('assets/brand/youssef-blog-og.png'))

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] shadow-soft transition duration-300 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow">
    <a href="{{ route('posts.show', $post) }}" class="block aspect-[16/9] overflow-hidden bg-[var(--surface-2)]">
        <img src="{{ $image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" width="640" height="360" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
    </a>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <a href="{{ route('categories.show', $post->category) }}" class="category-pill">{{ $post->category?->name }}</a>
        <a href="{{ route('posts.show', $post) }}" class="mt-4 block text-xl font-black leading-tight text-[var(--text)] transition hover:text-[var(--accent)]">{{ $post->shortAnchorTitle() }}</a>
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-[var(--muted)]">{{ $post->excerpt }}</p>
        <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-6 text-xs font-bold text-[var(--muted)]">
            <span class="flex min-w-0 items-center gap-2">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--text)] text-[10px] font-black text-[var(--bg)]">YY</span>
                <span class="truncate">{{ $post->user?->name ?: 'Youssef Youyou' }}</span>
            </span>
            <span>{{ $post->readingMinutes() }} min read</span>
        </div>
        <div class="mt-4 flex items-center justify-between gap-3 border-t border-[var(--border)] pt-4">
            <time class="text-xs font-bold text-[var(--muted)]" datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('M d, Y') }}</time>
            <a href="{{ route('posts.show', $post) }}" class="text-sm font-black text-[var(--accent)]">Read guide</a>
        </div>
    </div>
</article>
