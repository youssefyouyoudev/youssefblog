<section {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl bg-black p-8 text-white shadow-glow']) }}>
    <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand/20 blur-3xl"></div>
    <div class="relative">
        <p class="text-xs font-black uppercase tracking-[0.24em] text-brand">Newsletter</p>
        <h2 class="mt-3 text-3xl font-black">Get smarter every week.</h2>
        <p class="mt-3 max-w-xl text-sm leading-6 text-white/70">Practical notes on AI tools, Laravel, SaaS, online income, hosting, dashboards, and digital systems from Youssef Youyou.</p>
        <form class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input type="email" placeholder="you@example.com" class="min-h-12 flex-1 rounded-xl border border-white/15 bg-white px-4 text-sm text-black outline-none" disabled>
            <button type="button" class="premium-button bg-brand text-black">Coming Soon</button>
        </form>
    </div>
</section>
