@props(['title' => 'See the main portfolio', 'description' => 'Explore case studies, SaaS concepts, dashboards, CRM/ERP systems, and premium business websites by Youssef Youyou.'])

<a href="{{ config('brand.portfolio_url') }}" {{ $attributes->merge(['class' => 'block rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-6 shadow-soft transition hover:-translate-y-1 hover:border-[var(--accent)] hover:shadow-glow']) }}>
    <p class="text-xs font-black uppercase tracking-wide text-[var(--accent)]">Portfolio</p>
    <h3 class="mt-3 text-xl font-black text-[var(--text)]">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $description }}</p>
</a>
