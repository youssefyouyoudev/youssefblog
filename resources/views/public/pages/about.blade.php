<x-layouts.public :seo="$seo">
    <section class="safe-container max-w-4xl py-12 sm:py-16">
        <p class="text-sm font-black uppercase tracking-wide text-[var(--accent)]">About</p>
        <h1 class="mt-4 text-[clamp(2rem,9vw,3.75rem)] font-black leading-tight tracking-tight text-[var(--text)]">Practical guides from a developer who builds real web projects.</h1>
        <div class="content-body mt-8 rounded-3xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-soft sm:p-8">
            <p>I'm Youssef Youyou, a full-stack developer based in Morocco. I build Laravel and React projects, business websites, dashboards, APIs, CRM/ERP workflows, SaaS MVPs, and practical automation systems for people who need software to carry real work.</p>
            <p>This blog is where I turn that work into clear guides. The goal is simple: explain web development, AI workflows, finance habits, SEO, freelancing, and digital business in a way beginners can use without pretending complex topics are magic.</p>
            <p>I avoid fake certainty. Some articles are tutorials, some are checklists, and some are business notes from the point of view of a builder. When a topic needs caution, such as finance or AI automation, I say so directly.</p>
            <p><strong>If you are building a serious Laravel site, SaaS product, dashboard, or business system, you can contact me through the site.</strong></p>
            <a href="{{ route('contact') }}" class="premium-button mt-4 bg-[var(--accent)] text-white">Contact Youssef</a>
        </div>
    </section>
</x-layouts.public>
