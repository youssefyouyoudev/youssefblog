@props(['url' => url()->current(), 'title' => config('brand.blog_name')])

@php
    $encodedUrl = urlencode($url);
    $encodedTitle = urlencode($title);
    $links = [
        ['LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url='.$encodedUrl],
        ['X', 'https://twitter.com/intent/tweet?url='.$encodedUrl.'&text='.$encodedTitle],
        ['Facebook', 'https://www.facebook.com/sharer/sharer.php?u='.$encodedUrl],
        ['WhatsApp', 'https://wa.me/?text='.urlencode($title.' '.$url)],
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }} aria-label="Share this page">
    @foreach ($links as [$label, $shareUrl])
        <a
            href="{{ $shareUrl }}"
            class="inline-flex min-h-11 items-center rounded-full border border-current/20 px-3 py-2 text-xs font-black transition hover:border-current"
            target="_blank"
            rel="nofollow noopener noreferrer"
            aria-label="Share on {{ $label }}"
        >{{ $label }}</a>
    @endforeach
</nav>
