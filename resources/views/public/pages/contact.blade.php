<x-layouts.public :seo="$seo">
    <section class="safe-container max-w-4xl py-12 sm:py-16">
        <div class="rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft sm:p-8">
            <p class="text-sm font-black uppercase text-[var(--accent)]">Contact</p>
            <h1 class="mt-3 text-[clamp(2rem,9vw,3rem)] font-black leading-tight text-[var(--text)]">Tell me what you want to build.</h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-[var(--muted)] sm:text-lg">Best fit for businesses that need a premium website, SaaS platform, dashboard, CRM/ERP system, API, automation layer, or Laravel build that feels ready for serious users.</p>
            <div class="mt-6 flex flex-col gap-3 min-[430px]:flex-row min-[430px]:flex-wrap">
                <a href="mailto:{{ config('brand.email') }}" class="premium-button bg-[var(--text)] text-[var(--bg)]">{{ config('brand.email') }}</a>
                <x-whatsapp-cta />
            </div>
            @if (session('status'))
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('contact.store') }}" class="mt-8 grid gap-4">
                @csrf
                <input name="name" value="{{ old('name') }}" placeholder="Name" class="min-h-12 border border-[var(--border)] bg-[var(--bg)] px-4 py-3 text-base text-[var(--text)]" required>
                <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="min-h-12 border border-[var(--border)] bg-[var(--bg)] px-4 py-3 text-base text-[var(--text)]" required>
                <textarea name="message" rows="6" placeholder="Message" class="border border-[var(--border)] bg-[var(--bg)] px-4 py-3 text-base text-[var(--text)]" required>{{ old('message') }}</textarea>
                @if ($errors->any())
                    <p class="text-sm font-bold text-red-600">{{ $errors->first() }}</p>
                @endif
                <button class="premium-button bg-[var(--accent)] text-white" type="submit">Send Message</button>
            </form>
        </div>
    </section>
</x-layouts.public>
