@props(['title' => 'Need a website, SaaS platform, dashboard, or AI workflow?', 'description' => 'Work with Youssef Youyou to turn strategy into a production-ready Laravel system.'])

<section {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl border border-white/10 bg-[#07110c] p-8 text-white shadow-glow']) }}>
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-400/70 to-transparent"></div>
    <div class="relative">
        <p class="text-xs font-black uppercase tracking-[0.24em] text-emerald-300">Work with Youssef</p>
        <h2 class="mt-3 max-w-3xl text-3xl font-black">{{ $title }}</h2>
        <p class="mt-4 max-w-2xl text-sm leading-6 text-white/70">{{ $description }}</p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <a href="{{ config('brand.start_project_url') }}" class="premium-button bg-emerald-600 text-white">Start a Project</a>
            <a href="{{ config('brand.portfolio_url') }}" class="premium-button border border-white/20 text-white hover:border-brand hover:text-brand">View Portfolio</a>
        </div>
    </div>
</section>
