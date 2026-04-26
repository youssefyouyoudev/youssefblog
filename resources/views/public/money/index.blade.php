<x-layouts.public :seo="$seo">
    <section class="bg-black text-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-brand">Affiliate Money Pages</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-tight sm:text-6xl">Best tools, hosting, devices, and finance comparisons for builders.</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-white/70">Practical comparison guides for Morocco and global readers, written to help you choose tools without hype or fake promises.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <x-affiliate-disclosure />
        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($moneyPages as $page)
                <a href="{{ route('money.show', $page['slug']) }}" class="group overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-xl">
                    <img src="{{ $page['image'] }}" alt="{{ $page['title'] }}" class="aspect-[16/9] w-full object-cover" width="700" height="394" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/brand/youssef-blog-og.png') }}';">
                    <div class="p-5">
                        <span class="text-xs font-black uppercase text-emerald-600">{{ $page['category'] }}</span>
                        <h2 class="mt-2 text-xl font-black group-hover:text-emerald-700">{{ $page['title'] }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $page['excerpt'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
        <x-hire-youssef-banner />
    </section>
</x-layouts.public>
