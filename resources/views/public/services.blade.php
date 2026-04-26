<x-layouts.public :seo="$seo">
    @php($brand = config('brand'))

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-emerald-600">Work with Youssef Youyou</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black tracking-tight sm:text-6xl">Premium websites, SaaS platforms, dashboards, and custom systems for serious businesses.</h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">{{ $brand['positioning'] }} Built with Laravel, Blade, Tailwind, MySQL, APIs, automation, and deployment discipline.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $brand['start_project_url'] }}" class="rounded-lg bg-black px-6 py-3 text-center text-sm font-black text-white">Start a Project</a>
                <x-whatsapp-cta />
                <a href="{{ $brand['portfolio_url'] }}" class="rounded-lg border border-black/10 bg-white px-6 py-3 text-center text-sm font-black text-black">View Portfolio</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($brand['services'] as $service => $description)
                <x-service-cta-card :title="$service" :description="$description">
                    <div class="mt-4 grid gap-2 text-sm text-slate-600">
                        <p><strong class="text-black">Problem solved:</strong> unclear digital presence, manual work, weak conversion, or fragmented operations.</p>
                        <p><strong class="text-black">What I build:</strong> a polished, responsive, business-ready system around the real workflow.</p>
                        <p><strong class="text-black">Best for:</strong> B2B/B2C businesses, founders, agencies, SMEs, and teams that need serious execution.</p>
                    </div>
                </x-service-cta-card>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <x-hire-youssef-banner />
    </section>
</x-layouts.public>
