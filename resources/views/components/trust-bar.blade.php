<section {{ $attributes->merge(['class' => 'border-y border-gray-200 bg-white']) }}>
    <div class="mx-auto grid max-w-7xl gap-3 px-4 py-4 text-sm font-semibold text-gray-500 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-gray-200 lg:px-8">
        @foreach (['Written by a working developer', 'No AI filler — real project experience', 'Updated regularly', 'AdSense-compliant editorial policy'] as $item)
            <div class="flex items-center gap-2 lg:px-4 first:lg:pl-0">
                <span class="font-black text-blue-600">✓</span>
                <span>{{ $item }}</span>
            </div>
        @endforeach
    </div>
</section>
