<section {{ $attributes->merge(['class' => 'rounded-lg border border-black/10 bg-black p-6 text-white shadow-sm']) }}>
    <p class="text-sm font-black uppercase text-brand">Author</p>
    <h2 class="mt-2 text-2xl font-black">Youssef Youyou</h2>
    <p class="mt-3 text-sm leading-6 text-white/70">Senior Full-Stack Developer in Morocco building premium websites, SaaS platforms, dashboards, APIs, CRM/ERP systems, and AI-enabled workflows for serious businesses.</p>
    <div class="mt-5 flex flex-wrap gap-3">
        <a href="{{ config('brand.portfolio_url') }}" class="rounded-lg bg-brand px-4 py-2 text-sm font-black text-black">View Portfolio</a>
        <a href="{{ config('brand.start_project_url') }}" class="rounded-lg border border-white/20 px-4 py-2 text-sm font-black text-white">Start a Project</a>
    </div>
</section>
