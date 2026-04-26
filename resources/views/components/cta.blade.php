@props(['variant' => 'hire'])

@php
    $copy = [
        'freelance' => ['Freelance Laravel support', 'Need this implemented cleanly? Work with Youssef on Laravel, dashboards, APIs, SaaS MVPs, and AI workflows.', 'Work With Youssef', config('brand.start_project_url')],
        'newsletter' => ['Get smarter every week', 'Join the coming newsletter for practical finance, AI, Laravel, and digital business guides.', 'Coming Soon', '#'],
        'hire' => ['Need a premium business system?', 'Get a website, SaaS platform, dashboard, CRM/ERP, or automation workflow built around real business goals.', 'Start a Project', config('brand.start_project_url')],
    ][$variant] ?? ['Work with Youssef', config('brand.positioning'), 'Start a Project', config('brand.start_project_url')];
@endphp

<section {{ $attributes->merge(['class' => 'rounded-3xl border border-emerald-500/20 bg-emerald-50 p-6 shadow-soft']) }}>
    <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-700">{{ $copy[0] }}</p>
    <p class="mt-3 text-sm leading-6 text-slate-700">{{ $copy[1] }}</p>
    <a href="{{ $copy[3] }}" class="premium-button mt-5 bg-black text-white">{{ $copy[2] }}</a>
</section>
