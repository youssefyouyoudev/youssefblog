<x-layouts.public :seo="$seo">
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-black/10 bg-white p-8 shadow-sm">
            <p class="text-sm font-black uppercase text-emerald-600">Contact</p>
            <h1 class="mt-3 text-4xl font-black">Tell me what you want to build.</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">Best fit for businesses that need a premium website, SaaS platform, dashboard, CRM/ERP system, API, automation layer, or Laravel build that feels ready for serious users.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="mailto:{{ config('brand.email') }}" class="inline-flex rounded-lg bg-black px-5 py-3 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-slate-900">{{ config('brand.email') }}</a>
                <x-whatsapp-cta />
            </div>
            @if (session('status'))
                <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('contact.store') }}" class="mt-8 grid gap-4">
                @csrf
                <input name="name" value="{{ old('name') }}" placeholder="Name" class="border border-black/10 px-4 py-3" required>
                <input name="email" type="email" value="{{ old('email') }}" placeholder="Email" class="border border-black/10 px-4 py-3" required>
                <textarea name="message" rows="6" placeholder="Message" class="border border-black/10 px-4 py-3" required>{{ old('message') }}</textarea>
                @if ($errors->any())
                    <p class="text-sm font-bold text-red-600">{{ $errors->first() }}</p>
                @endif
                <button class="rounded-lg bg-black px-5 py-3 text-sm font-black text-white">Send Message</button>
            </form>
        </div>
    </section>
</x-layouts.public>
