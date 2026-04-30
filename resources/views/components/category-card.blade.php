@props(['name', 'description', 'url', 'count' => null, 'icon' => null])

<article {{ $attributes->merge(['class' => 'group rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-soft transition duration-300 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--accent)] text-white transition group-hover:-translate-y-0.5">
        @switch($icon)
            @case('chart')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V5"/><path d="M4 19h16"/><path d="m7 15 4-4 3 3 5-7"/></svg>
                @break
            @case('cpu')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="7" y="7" width="10" height="10" rx="2"/><path d="M4 9h3M4 15h3M17 9h3M17 15h3M9 4v3M15 4v3M9 17v3M15 17v3"/></svg>
                @break
            @case('sparkle')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3 2.3 6.7L21 12l-6.7 2.3L12 21l-2.3-6.7L3 12l6.7-2.3L12 3Z"/></svg>
                @break
            @case('code')
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m8 9-4 3 4 3"/><path d="m16 9 4 3-4 3"/><path d="m14 5-4 14"/></svg>
                @break
            @default
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
        @endswitch
    </span>
    <a href="{{ $url }}" class="mt-5 block text-xl font-black text-[var(--text)] transition hover:text-[var(--accent)]">{{ $name }}</a>
    <span class="mt-3 block text-sm leading-6 text-[var(--muted)]">{{ $description }}</span>
    @if (! is_null($count))
        <span class="mt-5 inline-flex rounded-full border border-[var(--border)] px-3 py-1 text-xs font-black uppercase text-[var(--muted)]">{{ $count }} guides</span>
    @endif
</article>
