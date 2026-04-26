@props(['post', 'large' => false])

<article {{ $attributes->merge(['class' => $large ? 'group overflow-hidden rounded-2xl border border-white/10 bg-white text-ink shadow-glow lg:row-span-2' : 'group overflow-hidden rounded-2xl border border-black/10 bg-white shadow-soft']) }}>
    @if ($post->featured_image)
        <a href="{{ route('posts.show', $post) }}" class="block overflow-hidden {{ $large ? 'aspect-[16/10]' : 'aspect-[16/9]' }} bg-slate-100">
            <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" width="{{ $large ? 960 : 640 }}" height="{{ $large ? 600 : 360 }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
        </a>
    @endif
    <div class="{{ $large ? 'p-7' : 'p-5' }}">
        <a href="{{ route('categories.show', $post->category) }}" class="category-pill">{{ $post->category->name }}</a>
        <h2 class="mt-4 font-black leading-tight {{ $large ? 'text-3xl' : 'text-xl' }}">
            <a href="{{ route('posts.show', $post) }}" class="transition hover:text-emerald-700">{{ $post->title }}</a>
        </h2>
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $post->excerpt }}</p>
        <div class="mt-5 flex flex-wrap items-center gap-3 text-xs font-bold text-slate-500">
            <span>Youssef Youyou</span>
            <span>{{ $post->readingMinutes() }} min read</span>
        </div>
    </div>
</article>
