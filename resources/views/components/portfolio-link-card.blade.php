@props(['title' => 'See the main portfolio', 'description' => 'Explore case studies, SaaS concepts, dashboards, CRM/ERP systems, and premium business websites by Youssef Youyou.'])

<a href="{{ config('brand.portfolio_url') }}" {{ $attributes->merge(['class' => 'block rounded-lg border border-black/10 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-lg']) }}>
    <p class="text-xs font-black uppercase tracking-wide text-emerald-600">Portfolio</p>
    <h3 class="mt-3 text-xl font-black">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p>
</a>
