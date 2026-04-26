<x-layouts.public :seo="$seo">
    @php($brand = config('brand'))
    <section class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[.8fr_1.2fr] lg:px-8">
        <div>
            <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-32 w-auto rounded-xl object-contain shadow-sm" width="300" height="132">
            <div class="mt-6 grid gap-3">
                <a href="{{ $brand['portfolio_url'] }}" class="rounded-lg bg-black px-5 py-3 text-center text-sm font-black text-white">View Portfolio</a>
                <a href="{{ $brand['start_project_url'] }}" class="rounded-lg bg-brand px-5 py-3 text-center text-sm font-black text-black">Start a Project</a>
            </div>
        </div>
        <div class="rounded-lg border border-black/10 bg-white p-8 shadow-sm">
            <p class="text-sm font-black uppercase text-emerald-600">About Youssef Blog</p>
            <h1 class="mt-3 text-4xl font-black">The content arm of Youssef Youyou’s full-stack business brand.</h1>
            <div class="content-body mt-6">
                <p>Youssef Youyou is a Senior Full-Stack Developer in Morocco building premium business websites, SaaS platforms, dashboards, CRM/ERP systems, APIs, automation layers, and custom business software for serious B2B and B2C clients.</p>
                <p>This blog exists to share practical thinking behind those systems: finance, tech, AI, Laravel, SaaS, online business, deployment, hosting, automation, and digital operations.</p>
                <p>The goal is not generic content. The goal is to help founders, agencies, SMEs, freelancers, and operators understand how better digital systems can win trust faster and reduce operational friction.</p>
                <p>Businesses can work with Youssef for premium websites, SaaS MVPs, dashboards, internal tools, Laravel development, APIs, CRM/ERP workflows, and AI-enabled systems.</p>
            </div>
        </div>
    </section>
    <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6 lg:px-8">
        <x-hire-youssef-banner />
    </section>
</x-layouts.public>
