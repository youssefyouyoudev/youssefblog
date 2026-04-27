@props(['post'])

<section {{ $attributes->merge(['class' => 'rounded-2xl border-l-4 border-blue-500 bg-blue-50 p-5 shadow-soft']) }}>
    <div class="flex gap-3">
        <svg class="mt-1 h-5 w-5 shrink-0 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        <p class="text-sm leading-6 text-gray-700">This post reflects hands-on experience from real Laravel projects. No affiliate link influences the editorial recommendations. Last reviewed: {{ $post->updated_at?->format('M d, Y') }}.</p>
    </div>
</section>
