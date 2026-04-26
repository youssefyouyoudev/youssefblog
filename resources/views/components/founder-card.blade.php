@props(['dark' => true])

@php($brand = config('brand'))

<section {{ $attributes->merge(['class' => $dark ? 'rounded-2xl border border-white/10 bg-white/5 p-6 text-white shadow-glow' : 'rounded-2xl border border-black/10 bg-white p-6 text-ink shadow-soft']) }}>
    <p class="text-xs font-black uppercase tracking-[0.24em] text-brand">Built by Youssef Youyou</p>
    <h2 class="mt-3 text-2xl font-black">Senior Full-Stack Developer helping businesses grow online.</h2>
    <p class="mt-3 text-sm leading-6 {{ $dark ? 'text-white/70' : 'text-slate-600' }}">Youssef Blog is the media arm of {{ $brand['name'] }}: practical finance, AI, Laravel, SaaS, and digital business insights from someone who builds production websites, dashboards, automation layers, and business systems.</p>
    <div class="mt-5 flex flex-wrap gap-3">
        <a href="{{ $brand['portfolio_url'] }}" class="premium-button bg-brand text-black">View Portfolio</a>
        <a href="{{ $brand['whatsapp_url'] }}" class="premium-button border {{ $dark ? 'border-white/20 text-white hover:border-brand hover:text-brand' : 'border-black/10 text-black hover:border-emerald-500' }}">WhatsApp</a>
    </div>
</section>
