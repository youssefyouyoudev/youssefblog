<x-layouts.public :seo="$seo">
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-emerald-600">Recommended Tools</p>
            <h1 class="mt-3 text-4xl font-black sm:text-5xl">Tools for building smarter in 2026</h1>
            <p class="mt-4 max-w-3xl text-lg leading-8 text-slate-600">A curated list of hosting, AI, developer, and finance tools that fit the Youssef Blog audience. Some links may become affiliate links and will be labeled clearly.</p>
            <x-affiliate-disclosure class="mt-6" />
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10">
            @foreach ($toolsByCategory as $category => $tools)
                <div>
                    <h2 class="text-2xl font-black">{{ $category }}</h2>
                    <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($tools as $tool)
                            <article class="rounded-lg border border-black/10 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-lg">
                                <p class="text-xs font-black uppercase text-emerald-600">{{ $tool->category }}</p>
                                <h3 class="mt-2 text-xl font-black">{{ $tool->name }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $tool->description }}</p>
                                @if ($tool->affiliate_url)
                                    <a href="{{ $tool->affiliate_url }}" rel="sponsored nofollow" class="mt-5 inline-flex rounded-lg bg-black px-4 py-2 text-sm font-black text-white">Visit tool</a>
                                @else
                                    <span class="mt-5 inline-flex rounded-lg bg-slate-100 px-4 py-2 text-sm font-black text-slate-600">No affiliate link</span>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.public>
