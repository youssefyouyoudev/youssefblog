@props(['headings' => null, 'content' => null])

@php
    $headings = $headings ?: collect();

    if ($headings->isEmpty() && $content) {
        preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>|^#{2,3}\s+(.+)$/mi', $content, $matches);
        $headings = collect($matches[1])
            ->merge($matches[2])
            ->map(fn ($heading) => trim(strip_tags($heading)))
            ->filter()
            ->unique(fn ($heading) => Str::slug($heading))
            ->values();
    }
@endphp

@if ($headings->isNotEmpty())
    <nav {{ $attributes->merge(['class' => 'rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft']) }}>
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">On this page</p>
        <div class="mt-4 grid gap-2 text-sm font-bold text-[var(--muted)]">
            @foreach ($headings as $heading)
                <a class="toc-link rounded-lg px-2 py-1 transition hover:bg-[var(--accent-soft)] hover:text-[var(--accent)]" data-toc-target="{{ Str::slug($heading) }}" href="#{{ Str::slug($heading) }}">{{ $heading }}</a>
            @endforeach
        </div>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const links = [...document.querySelectorAll('.toc-link')];
            const sections = links.map(link => document.getElementById(link.dataset.tocTarget)).filter(Boolean);
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    links.forEach(link => link.classList.remove('bg-[var(--accent-soft)]', 'text-[var(--accent)]'));
                    document.querySelector(`[data-toc-target="${entry.target.id}"]`)?.classList.add('bg-[var(--accent-soft)]', 'text-[var(--accent)]');
                });
            }, { rootMargin: '-20% 0px -70% 0px' });
            sections.forEach(section => observer.observe(section));
        });
    </script>
@endif
