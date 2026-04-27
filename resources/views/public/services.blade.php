<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $services = [
            ['code', 'Laravel Development', 'Production Laravel apps with Blade, queues, scheduler, auth, admin panels, APIs, and deployment discipline.'],
            ['sparkle', 'SaaS MVP', 'A focused product foundation with onboarding, dashboards, core workflows, and clean architecture.'],
            ['chart', 'Business Website', 'A premium website that explains the offer, earns trust fast, and gives prospects a clear next step.'],
            ['briefcase', 'CRM / ERP Dashboard', 'Internal tools for leads, operations, inventory, reporting, approvals, and team workflows.'],
        ];
    @endphp

    <section class="hero-grid bg-[#050505] text-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-blue-300">Work with Youssef Youyou</p>
            <h1 class="mt-4 max-w-4xl text-5xl font-black tracking-tight sm:text-7xl">Let's Build Something That Actually Works.</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-white/70">I don't take every project. I work with founders and companies who care about quality, speed, and long-term maintainability.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($services as [$icon, $title, $description])
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-soft">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white">
                        <span class="text-sm font-black">{{ Str::of($title)->words(1, '') }}</span>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-gray-600">{{ $description }}</p>
                    <a href="{{ $brand['start_project_url'] }}" class="mt-5 inline-flex text-sm font-black text-blue-600" rel="noopener noreferrer">Discuss this build →</a>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 pb-8 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-8">
            {{-- testimonial slot --}}
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gray-900 p-8 text-white shadow-glow">
            <h2 class="text-3xl font-black">Usually respond within 24 hours.</h2>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $brand['whatsapp_url'] }}" class="premium-button bg-blue-600 text-white" rel="noopener noreferrer">WhatsApp</a>
                <a href="mailto:{{ $brand['email'] }}" class="premium-button border border-white/20 text-white" rel="noopener noreferrer">{{ $brand['email'] }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>
