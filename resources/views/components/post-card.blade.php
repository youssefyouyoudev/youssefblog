@props(['post'])

@php
    $badge = [
        'laravel' => 'border-red-200 bg-red-50 text-red-700',
        'ai' => 'border-purple-200 bg-purple-50 text-purple-700',
        'finance' => 'border-green-200 bg-green-50 text-green-700',
        'tech' => 'border-blue-200 bg-blue-50 text-blue-700',
        'business' => 'border-orange-200 bg-orange-50 text-orange-700',
    ][$post->category?->slug] ?? 'border-blue-200 bg-blue-50 text-blue-700';
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-black/10 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:border-blue-600 hover:shadow-lg">
    @if ($post->featured_image)
        <a href="{{ route('posts.show', $post) }}" class="block aspect-video overflow-hidden bg-slate-100">
            <img src="{{ $post->featured_image }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" width="640" height="360" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
        </a>
    @endif
    <div class="flex flex-1 flex-col p-5">
        <span class="inline-flex w-fit rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-wide {{ $badge }}">{{ $post->category->name }}</span>
        <a href="{{ route('posts.show', $post) }}" class="mt-3 block text-xl font-black leading-tight hover:text-emerald-700">{{ $post->shortAnchorTitle() }}</a>
        <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $post->excerpt }}</p>
        <div class="mt-auto flex items-center justify-between pt-5 text-xs font-semibold text-slate-500">
            <span class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-black text-[10px] font-black text-brand">YY</span>
                <span>{{ $post->user?->name ?: 'Youssef Youyou' }}</span>
            </span>
            <span>{{ $post->readingMinutes() }} min</span>
        </div>
        <p class="mt-3 text-xs font-bold text-slate-400">{{ $post->published_at?->format('M d, Y') }}</p>
    </div>
</article>
