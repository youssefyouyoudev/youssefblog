@props(['page'])

<article {{ $attributes->merge(['class' => 'group rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft transition duration-300 hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow']) }}>
    <span class="category-pill">{{ $page['category'] }}</span>
    <a href="{{ route('money.show', $page['slug']) }}" class="mt-4 block text-lg font-black text-[var(--text)] transition hover:text-[var(--accent)]">{{ Str::words($page['title'], 6, '') }}</a>
    <span class="mt-3 line-clamp-3 block text-sm leading-6 text-[var(--muted)]">{{ $page['excerpt'] }}</span>
</article>
