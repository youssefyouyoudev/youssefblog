@props(['title', 'description', 'url' => null, 'label' => 'Discuss this build'])

<article {{ $attributes->merge(['class' => 'rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow']) }}>
    <p class="text-xs font-black uppercase tracking-wide text-[var(--accent)]">Service</p>
    <h3 class="mt-3 text-xl font-black text-[var(--text)]">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $description }}</p>
    {{ $slot }}
    <a href="{{ $url ?: config('brand.start_project_url') }}" class="premium-button mt-5 bg-[var(--text)] text-[var(--bg)]">{{ $label }}</a>
</article>
