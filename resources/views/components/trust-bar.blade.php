<section {{ $attributes->merge(['class' => 'border-y border-[var(--border)] bg-[var(--surface)]']) }}>
    <div class="safe-container grid gap-3 py-4 text-sm font-semibold text-[var(--muted)] sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-[var(--border)]">
        @foreach (['Written by a working developer', 'No filler, real project experience', 'Updated regularly', 'AdSense-compliant editorial policy'] as $item)
            <div class="flex items-center gap-2 lg:px-4 first:lg:pl-0">
                <span class="font-black text-[var(--accent)]">✓</span>
                <span>{{ $item }}</span>
            </div>
        @endforeach
    </div>
</section>
