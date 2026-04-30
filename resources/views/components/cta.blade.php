@props(['variant' => 'hire'])

@php
    $copy = [
        'freelance' => ['Freelance Laravel support', 'Need this implemented cleanly? Work with Youssef on Laravel, dashboards, APIs, SaaS MVPs, and AI workflows.', 'Work With Youssef', config('brand.start_project_url')],
        'newsletter' => ['Get smarter every week', 'Join the coming newsletter for practical finance, AI, Laravel, and digital business guides.', 'Coming Soon', '#'],
        'hire' => ['Need a premium business system?', 'Get a website, SaaS platform, dashboard, CRM/ERP, or automation workflow built around real business goals.', 'Start a Project', config('brand.start_project_url')],
    ][$variant] ?? ['Work with Youssef', config('brand.positioning'), 'Start a Project', config('brand.start_project_url')];
@endphp

<section {{ $attributes->merge(['class' => 'rounded-3xl border border-[var(--border)] bg-[var(--accent-soft)] p-6 shadow-soft']) }}>
    <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">{{ $copy[0] }}</p>
    <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $copy[1] }}</p>
    <a href="{{ $copy[3] }}" class="premium-button mt-5 bg-[var(--text)] text-[var(--bg)]">{{ $copy[2] }}</a>
</section>
