<section {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-3xl bg-gray-900 p-8 text-white shadow-glow']) }}>
    <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"></div>
    <div class="relative">
        <p class="text-xs font-black uppercase tracking-[0.24em] text-blue-300">Newsletter</p>
        <h2 class="mt-3 text-3xl font-black">Get Updates Weekly</h2>
        <p class="mt-3 max-w-xl text-sm leading-6 text-white/70">One useful email per week about Laravel, AI, SaaS, finance, and digital business systems. Nothing noisy.</p>
        <form class="newsletter-form mt-6 grid gap-3 sm:grid-cols-[1fr_auto]" method="POST" action="{{ route('contact.store') }}">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
            <input type="hidden" name="name" value="Newsletter subscriber">
            <input type="hidden" name="message" value="Newsletter subscription request from Youssef Blog. Please add this email to the newsletter list.">
            <input type="email" name="email" required placeholder="you@example.com" class="min-h-12 rounded-xl border border-white/15 bg-white px-4 text-sm text-black outline-none">
            <button class="premium-button bg-blue-600 text-white" type="submit">Subscribe</button>
            <p class="newsletter-status hidden text-sm font-bold sm:col-span-2"></p>
        </form>
    </div>
</section>
