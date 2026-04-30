@props(['post', 'large' => false])

@php($image = $post->featured_image ?: asset('assets/brand/youssef-blog-og.png'))

<article {{ $attributes->merge(['class' => ($large ? 'group overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] text-[var(--text)] shadow-glow lg:row-span-2' : 'group overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] shadow-soft').' transition duration-300 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow']) }}>
    <a href="{{ route('posts.show', $post) }}" class="block overflow-hidden {{ $large ? 'aspect-[16/10]' : 'aspect-[16/9]' }} bg-[var(--surface-2)]">
        <img src="{{ $image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" width="{{ $large ? 960 : 640 }}" height="{{ $large ? 600 : 360 }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
    </a>
    <div class="{{ $large ? 'p-7' : 'p-5' }}">
        <a href="{{ route('categories.show', $post->category) }}" class="category-pill">{{ $post->category?->name }}</a>
        <a href="{{ route('posts.show', $post) }}" class="mt-4 block font-black leading-snug text-[var(--text)] transition hover:text-[var(--accent)] {{ $large ? 'text-3xl' : 'text-xl' }}">{{ $post->title }}</a>
        <p class="mt-3 line-clamp-2 text-sm leading-6 text-[var(--muted)]">{{ $post->excerpt }}</p>
        <div class="mt-5 flex flex-wrap items-center gap-3 text-xs font-bold text-[var(--muted)]">
            <span>Youssef Youyou</span>
            <span>{{ $post->readingMinutes() }} min read</span>
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('M d, Y') }}</time>
        </div>
    </div>
</article>
