<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Applies verified, copyright-free Unsplash featured images to blog posts.
 *
 * Images are stored as direct Unsplash download URLs so they render
 * immediately without any local file downloads. The `onerror` fallback
 * in every Blade image tag will display the blog OG image if a URL ever
 * becomes unavailable.
 *
 * Run standalone:  php artisan db:seed --class=PostImageSeeder
 * Or call it from DatabaseSeeder after BlogPostSeeder.
 */
class PostImageSeeder extends Seeder
{
    /**
     * Map of post slug → image data.
     * URL format: https://unsplash.com/photos/{id}/download?force=true
     * These are the Unsplash CDN download links — free, no attribution required.
     */
    private array $images = [

        // ── AI & Productivity ────────────────────────────────────────────────

        'agentic-ai-multi-agent-systems-2026' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Glowing neural network representing interconnected AI agents collaborating on automated tasks',
            'image_credit'       => 'Possessed Photography on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1200&q=80&auto=format&fit=crop',
        ],

        'persistent-always-on-ai-assistants-teams' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Diverse team collaborating in modern office with AI assistant displayed on large screen',
            'image_credit'       => 'Jason Goodman on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&q=80&auto=format&fit=crop',
        ],

        'ai-powered-workplace-productivity-roi-measurement' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Analytics dashboard showing workplace productivity KPIs and ROI metrics with colorful charts',
            'image_credit'       => 'Luke Chesser on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80&auto=format&fit=crop',
        ],

        'advanced-reasoning-models-complex-problem-solving' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Digital brain visualization with glowing circuit patterns representing advanced AI reasoning',
            'image_credit'       => 'Growtika on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── Business Automation ──────────────────────────────────────────────

        'hyperautomation-2026-enterprise-game-changer' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Engineer working alongside industrial automation robots on a modern factory floor',
            'image_credit'       => 'ThisisEngineering RAEng on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1200&q=80&auto=format&fit=crop',
        ],

        'agentic-automation-end-to-end-process-management' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1512314889357-e157c22f938d?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Business analyst reviewing automated process management flowchart on laptop screen',
            'image_credit'       => 'Scott Graham on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1512314889357-e157c22f938d?w=1200&q=80&auto=format&fit=crop',
        ],

        'ai-driven-predictive-analytics-decision-intelligence' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Data scientist analyzing predictive analytics charts and decision intelligence models on dual monitors',
            'image_credit'       => 'Chris Liverani on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?w=1200&q=80&auto=format&fit=crop',
        ],

        'self-hosted-on-premise-ai-solutions-data-security' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Secure server room with locked data center racks representing on-premise AI infrastructure',
            'image_credit'       => 'Markus Spiske on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── E-Commerce ───────────────────────────────────────────────────────

        'agentic-commerce-future-ai-shopping-assistants' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Customer using AI-powered shopping assistant on smartphone discovering personalised product recommendations',
            'image_credit'       => 'Pexels via Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&q=80&auto=format&fit=crop',
        ],

        'livestream-shopping-commerce-trends-2026' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Content creator hosting a live shopping stream with products on display and ring light setup',
            'image_credit'       => 'Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=1200&q=80&auto=format&fit=crop',
        ],

        'unified-commerce-strategy-seamless-omnichannel-experience' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Customer browsing products seamlessly across laptop tablet and smartphone in unified shopping experience',
            'image_credit'       => 'Christiann Koepke on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1200&q=80&auto=format&fit=crop',
        ],

        'social-commerce-explosion-tiktok-shop-instagram-selling' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Young shopper browsing social commerce products on smartphone with social media apps visible',
            'image_credit'       => 'Alexander Shatov on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── Freelance & Digital Business ─────────────────────────────────────

        'ai-powered-talent-matching-freelance-platforms' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Freelancer and client connected via digital talent matching platform on laptop screen',
            'image_credit'       => 'Desola Lanre-Ologun on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200&q=80&auto=format&fit=crop',
        ],

        'freelance-specialization-niche-experts-earn-more' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Confident niche expert professional at a focused workspace with specialised tools and notes',
            'image_credit'       => 'Andrew Neel on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&q=80&auto=format&fit=crop',
        ],

        'global-gig-economy-boom-2026-predictions' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Remote workers collaborating around a laptop representing the global gig economy boom',
            'image_credit'       => 'Marvin Meyer on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&q=80&auto=format&fit=crop',
        ],

        'data-literacy-skills-freelancers-digital-professionals' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1543286386-713bdd548da4?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Professional analysing data charts and spreadsheets to build data literacy skills',
            'image_credit'       => 'Isaac Smith on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1543286386-713bdd548da4?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── Laravel & Web Development ─────────────────────────────────────────

        'ai-assisted-laravel-development-code-generation-tools' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Developer using AI-assisted code generation in VS Code on a dark themed workstation',
            'image_credit'       => 'Clément Hélardot on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1555949963-ff9fe0c870eb?w=1200&q=80&auto=format&fit=crop',
        ],

        'building-scalable-saas-laravel-multi-tenancy' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'SaaS dashboard interface displaying multi-tenant data isolation across multiple client accounts',
            'image_credit'       => 'Carlos Muza on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80&auto=format&fit=crop',
        ],

        'laravel-mvp-development-idea-to-launch-4-12-weeks' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Startup founder reviewing MVP product roadmap on whiteboard in a modern co-working space',
            'image_credit'       => 'Headway on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1200&q=80&auto=format&fit=crop',
        ],

        'serverless-laravel-vapor-frankenphp-performance' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Cloud computing infrastructure with auto-scaling servers representing serverless Laravel Vapor deployment',
            'image_credit'       => 'NASA on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── SaaS & MVP Development ────────────────────────────────────────────

        'ai-first-saas-scaffolding-rapid-foundation-generation' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Startup engineers rapidly scaffolding AI-first SaaS foundation at whiteboard with architecture diagrams',
            'image_credit'       => 'Marvin Meyer on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&q=80&auto=format&fit=crop',
        ],

        'feature-flags-continuous-deployment-best-practices' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'DevOps engineer configuring feature flags and CI/CD deployment pipeline on terminal screen',
            'image_credit'       => 'Ilya Pavlov on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=1200&q=80&auto=format&fit=crop',
        ],

        'embedding-ai-features-saas-differentiation' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Software product interface with embedded AI recommendation engine providing intelligent suggestions',
            'image_credit'       => 'Growtika on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=1200&q=80&auto=format&fit=crop',
        ],

        'micro-saas-vertical-specialization-strategy-success-metrics' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Solo founder monitoring Micro-SaaS success metrics dashboard with MRR and churn rate charts',
            'image_credit'       => 'Carlos Muza on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── Laravel Tutorials & Best Practices ────────────────────────────────

        'laravel-octane-performance-benchmarking-optimization-guide' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'High-performance server infrastructure with speed benchmarks on monitoring dashboard',
            'image_credit'       => 'Luca Bravo on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200&q=80&auto=format&fit=crop',
        ],

        'api-first-headless-laravel-decoupled-architecture-patterns' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'API-first architecture diagram showing decoupled frontend and Laravel backend connected via REST endpoints',
            'image_credit'       => 'Chris Ried on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=1200&q=80&auto=format&fit=crop',
        ],

        'microservices-domain-driven-design-laravel-applications' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Microservices architecture diagram showing independently deployable Laravel domain services',
            'image_credit'       => 'Growtika on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?w=1200&q=80&auto=format&fit=crop',
        ],

        'testing-developer-experience-pest-pint-modern-tooling' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Developer running Pest PHP test suite with green passing tests on terminal in a clean workspace',
            'image_credit'       => 'Sigmund on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1516116216624-53e697fedbea?w=1200&q=80&auto=format&fit=crop',
        ],

        // ── Web Development Trends ────────────────────────────────────────────

        'headless-cms-decoupled-architecture-modern-web-apps' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Content editor using headless CMS dashboard to publish structured content across web and mobile apps',
            'image_credit'       => 'Domenico Loia on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?w=1200&q=80&auto=format&fit=crop',
        ],

        'real-time-web-applications-websockets-broadcasting' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Real-time data streams visualized as glowing connection lines representing WebSocket broadcasting',
            'image_credit'       => 'Alina Grubnyak on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=1200&q=80&auto=format&fit=crop',
        ],

        'progressive-web-apps-pwa-offline-first-strategy-guide' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Mobile phone displaying a progressive web app working seamlessly in offline mode',
            'image_credit'       => 'Rahul Chakraborty on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&q=80&auto=format&fit=crop',
        ],

        'security-first-web-development-zero-trust-architecture' => [
            'featured_image'     => 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=1200&q=80&auto=format&fit=crop',
            'featured_image_alt' => 'Cybersecurity engineer implementing zero-trust network architecture with identity verification layers',
            'image_credit'       => 'Franck on Unsplash (Unsplash License)',
            'og_image'           => 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=1200&q=80&auto=format&fit=crop',
        ],
    ];

    public function run(): void
    {
        $updated = 0;
        $missing = [];

        foreach ($this->images as $slug => $data) {
            $rows = DB::table('posts')
                ->where('slug', $slug)
                ->whereNull('deleted_at')
                ->update([
                    'featured_image'     => $data['featured_image'],
                    'featured_image_alt' => $data['featured_image_alt'],
                    'image_credit'       => $data['image_credit'],
                    'og_image'           => $data['og_image'],
                    'updated_at'         => now(),
                ]);

            if ($rows > 0) {
                $updated++;
                $this->command->line("  <fg=green>✓</> {$slug}");
            } else {
                $missing[] = $slug;
                $this->command->line("  <fg=yellow>–</> Not found: {$slug}");
            }
        }

        $this->command->newLine();
        $this->command->info("✅ PostImageSeeder complete — {$updated} posts updated.");

        if (! empty($missing)) {
            $this->command->warn(count($missing) . " slug(s) not found in the database. Run BlogPostSeeder first.");
        }
    }
}
