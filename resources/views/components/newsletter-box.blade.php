<section {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-8 text-[var(--text)] shadow-soft']) }}>
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[var(--accent)] via-transparent to-transparent"></div>
    <div class="relative">
        <p class="text-xs font-black uppercase tracking-[0.24em] text-[var(--accent)]">Newsletter</p>
        <h2 class="mt-3 text-3xl font-black">Get Updates Weekly</h2>
        <p class="mt-3 max-w-xl text-sm leading-6 text-[var(--muted)]">One useful email per week about Laravel, AI, SaaS, finance, and digital business systems. Nothing noisy.</p>
        <form class="newsletter-form mt-6 grid gap-3 sm:grid-cols-[1fr_auto]" method="POST" action="{{ route('contact.store') }}">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
            <input type="hidden" name="name" value="Newsletter subscriber">
            <input type="hidden" name="message" value="Newsletter subscription request from Youssef Blog. Please add this email to the newsletter list.">
            <input type="email" name="email" required placeholder="you@example.com" class="min-h-12 rounded-xl border border-[var(--border)] bg-[var(--bg)] px-4 text-sm text-[var(--text)] outline-none focus:border-[var(--accent)]">
            <button class="premium-button bg-[var(--accent)] text-white" type="submit">Subscribe</button>
            <p class="newsletter-status hidden text-sm font-bold sm:col-span-2"></p>
        </form>
    </div>
</section>
