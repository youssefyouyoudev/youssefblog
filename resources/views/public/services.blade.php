<x-layouts.public :seo="$seo">
    @php
        $brand = config('brand');
        $services = [
            ['Laravel', 'Laravel Development', 'Production Laravel apps with Blade, queues, scheduler, auth, admin panels, APIs, and deployment discipline.'],
            ['SaaS', 'SaaS MVP', 'A focused product foundation with onboarding, dashboards, core workflows, and clean architecture.'],
            ['Web', 'Business Website', 'A premium website that explains the offer, earns trust fast, and gives prospects a clear next step.'],
            ['CRM', 'CRM / ERP Dashboard', 'Internal tools for leads, operations, inventory, reporting, approvals, and team workflows.'],
        ];
    @endphp

    <section class="hero-grid text-white">
        <div class="safe-container max-w-6xl py-14 sm:py-16">
            <p class="text-sm font-black uppercase tracking-wide text-emerald-300">Work with Youssef Youyou</p>
            <h1 class="mt-4 max-w-4xl text-[clamp(2.25rem,10vw,4.5rem)] font-black leading-tight tracking-tight">Let's Build Something That Actually Works.</h1>
            <p class="mt-6 max-w-3xl text-base leading-8 text-white/70 sm:text-lg">I don't take every project. I work with founders and companies who care about quality, speed, and long-term maintainability.</p>
        </div>
    </section>

    <section class="safe-container py-12 lg:py-14">
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($services as [$icon, $title, $description])
                <div class="rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft sm:p-6">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--accent)] text-sm font-black text-white">
                        {{ $icon }}
                    </div>
                    <h2 class="text-2xl font-black text-[var(--text)]">{{ $title }}</h2>
                    <p class="mt-3 text-sm leading-6 text-[var(--muted)]">{{ $description }}</p>
                    <a href="{{ $brand['start_project_url'] }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-black text-[var(--accent)]" rel="noopener noreferrer">Discuss this build -></a>
                </div>
            @endforeach
        </div>
    </section>

    <section class="safe-container max-w-5xl pb-8">
        <div class="rounded-3xl border border-dashed border-[var(--border)] bg-[var(--surface)] p-6 sm:p-8">
            {{-- testimonial slot --}}
        </div>
    </section>

    <section class="safe-container max-w-5xl pb-14 sm:pb-16">
        <div class="rounded-3xl border border-white/10 bg-[#07110c] p-6 text-white shadow-glow sm:p-8">
            <h2 class="text-3xl font-black">Usually respond within 24 hours.</h2>
            <div class="mt-6 flex flex-col gap-3 min-[430px]:flex-row min-[430px]:flex-wrap">
                <a href="{{ $brand['whatsapp_url'] }}" class="premium-button bg-emerald-600 text-white" rel="noopener noreferrer">WhatsApp</a>
                <a href="mailto:{{ $brand['email'] }}" class="premium-button border border-white/20 text-white" rel="noopener noreferrer">{{ $brand['email'] }}</a>
            </div>
        </div>
    </section>
</x-layouts.public>
