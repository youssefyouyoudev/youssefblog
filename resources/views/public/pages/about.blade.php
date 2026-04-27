<x-layouts.public :seo="$seo">
    @php($brand = config('brand'))
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-black uppercase tracking-wide text-blue-600">About</p>
        <h1 class="mt-4 text-4xl font-black tracking-tight text-gray-900 sm:text-6xl">I'm Youssef Youyou — I build things that work.</h1>
        <div class="content-body mt-8 rounded-3xl border border-gray-200 bg-white p-8 shadow-soft">
            <p>I'm a Senior Full-Stack Laravel Developer based in Morocco. Over 5+ years and 25+ projects, I have built Laravel SaaS products, business websites, dashboards, APIs, CRM/ERP workflows, and internal tools for people who need software to carry real work, not just look good in a screenshot.</p>
            <p>This blog exists because writing forces clearer thinking. It is not here only to rank on Google. Developers in Morocco and globally deserve honest technical content without hype, fake certainty, or recycled advice that has never touched a production project.</p>
            <p>I write about finance, tech, AI, Laravel, and business because those topics meet in real digital products. A good SaaS needs engineering, positioning, cost control, automation, and a clear understanding of how people actually work.</p>
            <p><strong>If you're building something serious and need a developer who ships, let's talk.</strong></p>
            <a href="{{ route('contact') }}" class="premium-button mt-4 bg-blue-600 text-white">Contact Youssef</a>
        </div>
    </section>
</x-layouts.public>
