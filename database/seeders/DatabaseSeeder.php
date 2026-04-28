<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@youssefyouyou.com'],
            [
                'name' => 'Youssef Youyou',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        $categories = collect([
            ['name' => 'Finance', 'description' => 'Beginner-friendly money systems, saving ideas, fintech tools, and investing education for freelancers and builders.'],
            ['name' => 'Tech', 'description' => 'Hosting, software, productivity, and practical tools for developers, creators, and small online businesses.'],
            ['name' => 'AI', 'description' => 'AI tools, ChatGPT workflows, and small-business AI agents explained in a practical, non-hype way.'],
            ['name' => 'Laravel', 'description' => 'Laravel SEO, deployment, SaaS ideas, and Blade-first product building for fast web launches.'],
            ['name' => 'Business', 'description' => 'Online business ideas, Moroccan freelancer systems, SaaS thinking, and ethical monetization guides.'],
        ])->mapWithKeys(fn (array $category): array => [
            $category['name'] => Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'seo_title' => $category['name'].' Guides for Builders',
                    'meta_description' => $category['description'],
                ],
            ),
        ]);

        $posts = $this->posts();
        $tagNames = collect($posts)->flatMap(fn (array $post): array => $post['tags'])->unique()->values();
        $tags = $tagNames->mapWithKeys(fn (string $name): array => [
            $name => Tag::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name]),
        ]);

        foreach ($posts as $index => $data) {
            $content = $this->content($data['title'], $data['category'], $data['angle']);

            $post = Post::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $admin->id,
                    'category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $content,
                    'featured_image' => $data['image'],
                    'featured_image_alt' => $data['image_alt'],
                    'image_credit' => $data['image_credit'],
                    'status' => 'published',
                    'published_at' => now()->subDays(20 - $index)->setTime(9, 0),
                    'seo_title' => $data['seo_title'],
                    'meta_description' => $data['meta_description'],
                    'keywords' => $data['keywords'],
                    'faqs' => $data['faqs'] ?? $this->faqs($data),
                    'og_image' => $data['image'],
                    'reading_time' => max(4, (int) ceil(Str::wordCount(strip_tags($content)) / 220)),
                    'is_featured' => in_array($data['slug'], [
                        'ai-tools-for-small-business-2026',
                        'laravel-seo-checklist-blade-apps',
                        'make-money-online-morocco-freelancers',
                    ], true),
                ],
            );

            $post->tags()->sync(collect($data['tags'])->map(fn (string $tag): int => $tags[$tag]->id));
        }

        foreach ($this->tools() as $tool) {
            Tool::updateOrCreate(['name' => $tool['name']], $tool);
        }

        $this->call(ProfessionalBlogPostSeeder::class);
    }

    private function posts(): array
    {
        return [
            [
                'category' => 'Finance',
                'title' => 'Finance Tips for Freelancers: A Simple Monthly Money Routine',
                'slug' => 'finance-tips-for-freelancers-monthly-money-routine',
                'seo_title' => 'Finance Tips for Freelancers: Simple Monthly Money Routine',
                'excerpt' => 'A monthly finance routine for freelancers who need predictable cash flow, tax buffers, and calm decisions.',
                'meta_description' => 'Use these finance tips for freelancers to build a monthly routine for income tracking, savings, tax buffers, and cash-flow planning.',
                'keywords' => ['finance tips for freelancers', 'freelancer money routine', 'cash flow planning', 'tax buffer', 'saving money 2026'],
                'tags' => ['Freelance Finance', 'Budgeting', 'Cash Flow'],
                'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Freelancer reviewing finance documents',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'turning irregular income into a calm monthly money routine',
            ],
            [
                'category' => 'Finance',
                'title' => 'How to Save Money in 2026 Without Killing Your Lifestyle',
                'slug' => 'save-money-2026-without-killing-your-lifestyle',
                'seo_title' => 'How to Save Money in 2026 Without Killing Your Lifestyle',
                'excerpt' => 'A simple saving system for freelancers, students, and builders who want control without extreme budgeting.',
                'meta_description' => 'Learn a beginner-friendly 2026 saving system with automation, spending rules, and fintech tools without extreme budgeting.',
                'keywords' => ['save money 2026', 'finance tips for freelancers', 'budgeting system', 'money habits', 'personal finance'],
                'tags' => ['Saving Money', 'Budgeting', 'Freelance Finance'],
                'image' => 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Person planning savings with money and calculator',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'saving money with automation and realistic rules',
            ],
            [
                'category' => 'Finance',
                'title' => 'Beginner Investing Education for Builders: What to Learn First',
                'slug' => 'beginner-investing-education-builders',
                'seo_title' => 'Beginner Investing Education for Builders: What to Learn First',
                'excerpt' => 'An educational roadmap for learning investing basics without risky promises or hype.',
                'meta_description' => 'A safe beginner investing education guide covering risk, diversification, fees, time horizon, and learning resources.',
                'keywords' => ['beginner investing education', 'investing basics', 'personal finance education', 'risk management', 'long term investing'],
                'tags' => ['Investing Education', 'Risk Management', 'Money Basics'],
                'image' => 'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Business people reviewing financial charts',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'learning investing fundamentals before making decisions',
            ],
            [
                'category' => 'Finance',
                'title' => 'Best Fintech Tools for Freelancers to Track Cash Flow',
                'slug' => 'best-fintech-tools-freelancers-cash-flow',
                'seo_title' => 'Best Fintech Tools for Freelancers to Track Cash Flow',
                'excerpt' => 'A practical cash-flow setup for freelancers who want fewer surprises and cleaner money decisions.',
                'meta_description' => 'Discover how freelancers can use fintech tools, spreadsheets, and simple routines to track cash flow in 2026.',
                'keywords' => ['fintech tools freelancers', 'cash flow tracking', 'finance tips for freelancers', 'money tools', 'freelancer budgeting'],
                'tags' => ['Fintech', 'Freelance Finance', 'Productivity'],
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Laptop with finance reports and calculator',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'cash-flow tracking with tools and a weekly review',
            ],
            [
                'category' => 'Tech',
                'title' => 'Best Laptop for Coding in 2026: What Developers Should Prioritize',
                'slug' => 'best-laptop-for-coding-2026',
                'seo_title' => 'Best Laptop for Coding in 2026: Developer Buying Guide',
                'excerpt' => 'A practical laptop buying guide for developers focused on performance, battery, screen quality, and longevity.',
                'meta_description' => 'Learn what to prioritize when choosing the best laptop for coding in 2026, including CPU, RAM, storage, display, battery, and budget.',
                'keywords' => ['best laptop for coding', 'developer laptop 2026', 'coding gear', 'developer tools', 'productivity'],
                'tags' => ['Developer Gear', 'Coding Tools', 'Productivity'],
                'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Laptop with code editor on desk',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'buying developer gear based on real work instead of hype',
            ],
            [
                'category' => 'Tech',
                'title' => 'Best Hosting Setup for a Fast Laravel Blog in 2026',
                'slug' => 'best-hosting-setup-fast-laravel-blog-2026',
                'seo_title' => 'Best Hosting Setup for a Fast Laravel Blog in 2026',
                'excerpt' => 'A practical hosting checklist for speed, uptime, backups, SSL, and clean Laravel deployment.',
                'meta_description' => 'Learn the best hosting setup for a fast Laravel blog in 2026, including VPS basics, SSL, caching, queues, and backups.',
                'keywords' => ['best hosting for Laravel', 'Laravel hosting 2026', 'fast Laravel blog', 'VPS hosting', 'Laravel deployment'],
                'tags' => ['Hosting', 'Laravel Deployment', 'Performance'],
                'image' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Server racks for web hosting',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'choosing hosting that is fast, maintainable, and affordable',
            ],
            [
                'category' => 'Tech',
                'title' => 'Developer Productivity Tools That Actually Save Time',
                'slug' => 'developer-productivity-tools-save-time',
                'seo_title' => 'Developer Productivity Tools That Actually Save Time',
                'excerpt' => 'A lean software stack for developers who want focus, automation, and fewer context switches.',
                'meta_description' => 'A practical 2026 guide to developer productivity tools for focus, automation, notes, terminal workflows, and shipping faster.',
                'keywords' => ['developer productivity tools', 'productivity software 2026', 'coding tools', 'automation tools', 'developer workflow'],
                'tags' => ['Productivity', 'Developer Tools', 'Automation'],
                'image' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Developer desk with code on monitor',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'building a tool stack that reduces context switching',
            ],
            [
                'category' => 'Tech',
                'title' => 'A Simple Gear and Software Stack for Online Creators',
                'slug' => 'simple-gear-software-stack-online-creators',
                'seo_title' => 'A Simple Gear and Software Stack for Online Creators',
                'excerpt' => 'The creator stack that covers writing, recording, publishing, and analytics without buying everything.',
                'meta_description' => 'Build a simple creator tech stack for 2026 with essential gear, software, backups, and publishing tools.',
                'keywords' => ['creator software stack', 'developer gear', 'online creator tools', 'content workflow', 'productivity tools'],
                'tags' => ['Creator Tools', 'Productivity', 'Software Stack'],
                'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Modern workspace for online creators',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'buying only the tools that support consistent publishing',
            ],
            [
                'category' => 'AI',
                'title' => 'Best AI Tools 2026: A Builder-Friendly Shortlist',
                'slug' => 'best-ai-tools-2026-builder-shortlist',
                'seo_title' => 'Best AI Tools 2026: Builder-Friendly Shortlist',
                'excerpt' => 'A practical shortlist of AI tool categories for research, writing, automation, coding, and business operations.',
                'meta_description' => 'Explore the best AI tools for 2026 by use case, including research, writing, coding, automation, and business workflows.',
                'keywords' => ['best AI tools 2026', 'AI tools 2026', 'ChatGPT workflows', 'AI automation', 'AI productivity tools'],
                'tags' => ['AI Tools', 'Productivity', 'Automation'],
                'image' => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'AI interface concept with data visualization',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'choosing AI tools by use case instead of collecting subscriptions',
            ],
            [
                'category' => 'AI',
                'title' => 'AI Tools for Small Business in 2026: A Practical Starter Stack',
                'slug' => 'ai-tools-for-small-business-2026',
                'seo_title' => 'AI Tools for Small Business in 2026: Practical Starter Stack',
                'excerpt' => 'A simple AI stack for research, customer support, content, operations, and decision support.',
                'meta_description' => 'Discover practical AI tools for small businesses in 2026, including ChatGPT workflows, automation, customer support, and content systems.',
                'keywords' => ['AI tools 2026', 'AI agents for business', 'ChatGPT workflows', 'small business AI', 'business automation'],
                'tags' => ['AI Tools', 'ChatGPT', 'Small Business'],
                'image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Abstract AI network visualization',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'using AI as a practical business assistant instead of a magic button',
            ],
            [
                'category' => 'AI',
                'title' => 'ChatGPT Workflow for Writing Better Blog Posts Faster',
                'slug' => 'chatgpt-workflow-writing-better-blog-posts',
                'seo_title' => 'ChatGPT Workflow for Writing Better Blog Posts Faster',
                'excerpt' => 'A repeatable workflow for research, outlines, drafts, editing, SEO, and human quality control.',
                'meta_description' => 'Use this ChatGPT workflow to research, outline, draft, edit, and optimize blog posts while keeping your own voice.',
                'keywords' => ['ChatGPT workflow', 'AI writing workflow', 'blog SEO workflow', 'AI content tools', 'write blog posts faster'],
                'tags' => ['ChatGPT', 'SEO', 'Content Workflow'],
                'image' => 'https://images.unsplash.com/photo-1679403766683-3bcd3b25d4f0?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Laptop with AI writing workspace',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'using ChatGPT for structure and review while preserving originality',
            ],
            [
                'category' => 'AI',
                'title' => 'AI Agents for Moroccan Small Businesses: Where to Start',
                'slug' => 'ai-agents-moroccan-small-businesses',
                'seo_title' => 'AI Agents for Moroccan Small Businesses: Where to Start',
                'excerpt' => 'Beginner-friendly AI agent ideas for invoices, customer messages, research, and daily operations.',
                'meta_description' => 'Learn how Moroccan small businesses can start with AI agents for support, admin work, lead research, and simple automation.',
                'keywords' => ['AI agents Morocco', 'AI agents for business', 'small business automation Morocco', 'AI tools 2026', 'business productivity'],
                'tags' => ['AI Agents', 'Morocco', 'Automation'],
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Small business team planning automation',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'starting AI automation with low-risk admin tasks',
            ],
            [
                'category' => 'Laravel',
                'title' => 'Laravel Hosting Guide: VPS, Shared Hosting, and Managed Platforms',
                'slug' => 'laravel-hosting-guide-vps-shared-managed',
                'seo_title' => 'Laravel Hosting Guide: VPS, Shared Hosting, Managed Platforms',
                'excerpt' => 'A practical Laravel hosting guide comparing VPS, shared hosting, managed platforms, and what to use first.',
                'meta_description' => 'Compare Laravel hosting options for 2026, including VPS, shared hosting, managed platforms, queues, SSL, backups, and deployment needs.',
                'keywords' => ['Laravel hosting guide', 'best VPS for Laravel', 'Laravel deployment', 'Laravel hosting 2026', 'managed Laravel hosting'],
                'tags' => ['Laravel Deployment', 'Hosting', 'VPS'],
                'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Data center servers for Laravel hosting',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'matching Laravel hosting options to project size and maintenance capacity',
            ],
            [
                'category' => 'Laravel',
                'title' => 'Laravel SEO Checklist for Blade-First Blogs',
                'slug' => 'laravel-seo-checklist-blade-apps',
                'seo_title' => 'Laravel SEO Checklist for Blade-First Blogs',
                'excerpt' => 'A Laravel SEO checklist covering metadata, slugs, schema, sitemaps, feeds, speed, and internal links.',
                'meta_description' => 'Use this Laravel SEO checklist for Blade-first blogs with meta tags, schema, sitemap.xml, robots.txt, RSS, and performance basics.',
                'keywords' => ['Laravel SEO', 'Blade SEO', 'Laravel blog SEO', 'sitemap Laravel', 'Article schema Laravel'],
                'tags' => ['Laravel SEO', 'Blade', 'Technical SEO'],
                'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Code editor showing web development project',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'making Laravel pages easy for crawlers and readers',
            ],
            [
                'category' => 'Laravel',
                'title' => 'Laravel Deployment Checklist for Small Production Apps',
                'slug' => 'laravel-deployment-checklist-small-production-apps',
                'seo_title' => 'Laravel Deployment Checklist for Small Production Apps',
                'excerpt' => 'A production deployment checklist for queues, storage, permissions, cache, SSL, backups, and monitoring.',
                'meta_description' => 'Deploy Laravel apps safely with this practical checklist for permissions, environment, SSL, queues, caching, backups, and Nginx.',
                'keywords' => ['Laravel deployment', 'deploy Laravel app', 'Nginx Laravel', 'Laravel production checklist', 'Laravel hosting'],
                'tags' => ['Laravel Deployment', 'Hosting', 'DevOps'],
                'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Circuit board representing production infrastructure',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'deploying with boring reliable production habits',
            ],
            [
                'category' => 'Laravel',
                'title' => 'Laravel SaaS Ideas You Can Build as a Solo Developer',
                'slug' => 'laravel-saas-ideas-solo-developer',
                'seo_title' => 'Laravel SaaS Ideas You Can Build as a Solo Developer',
                'excerpt' => 'Practical SaaS ideas for solo Laravel developers, with validation tips and monetization paths.',
                'meta_description' => 'Explore Laravel SaaS ideas for solo developers in 2026, including niche tools, validation, MVP scope, and monetization strategy.',
                'keywords' => ['Laravel SaaS ideas', 'SaaS ideas Morocco', 'solo developer SaaS', 'Laravel business ideas', 'micro SaaS'],
                'tags' => ['SaaS', 'Laravel', 'Startup Ideas'],
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Analytics dashboard for SaaS product planning',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'choosing SaaS ideas with a narrow user and fast validation',
            ],
            [
                'category' => 'Business',
                'title' => 'Blogging Income Guide 2026: From Helpful Content to Revenue',
                'slug' => 'blogging-income-guide-2026-helpful-content-revenue',
                'seo_title' => 'Blogging Income Guide 2026: Helpful Content to Revenue',
                'excerpt' => 'A realistic blogging income guide covering content hubs, SEO, affiliate offers, AdSense readiness, and trust.',
                'meta_description' => 'Learn a practical blogging income strategy for 2026 with helpful content, SEO hubs, affiliate links, AdSense readiness, and trust signals.',
                'keywords' => ['blogging income guide', 'blog monetization', 'affiliate marketing SEO', 'AdSense readiness', 'online income 2026'],
                'tags' => ['Blogging', 'Monetization', 'SEO'],
                'image' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Writing desk for blogging and content planning',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'building revenue by helping readers before monetizing attention',
            ],
            [
                'category' => 'Business',
                'title' => 'How Moroccan Freelancers Can Make Money Online in 2026',
                'slug' => 'make-money-online-morocco-freelancers',
                'seo_title' => 'How Moroccan Freelancers Can Make Money Online in 2026',
                'excerpt' => 'A practical roadmap for Moroccan freelancers to package skills, find clients, and build online income ethically.',
                'meta_description' => 'A beginner-friendly guide for Moroccan freelancers to make money online in 2026 with services, content, portfolios, and client systems.',
                'keywords' => ['make money online Morocco', 'Moroccan freelancers', 'online business Morocco', 'freelance income', 'side hustle Morocco'],
                'tags' => ['Morocco', 'Freelancing', 'Online Income'],
                'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Freelancers working together on laptops',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'turning skills into a clear service offer and client pipeline',
            ],
            [
                'category' => 'Business',
                'title' => 'Online Business Ideas for Developers Who Like Building Systems',
                'slug' => 'online-business-ideas-developers-building-systems',
                'seo_title' => 'Online Business Ideas for Developers Who Like Building Systems',
                'excerpt' => 'Business ideas that fit developers: templates, niche software, content sites, automation, and productized services.',
                'meta_description' => 'Explore online business ideas for developers in 2026, from productized services and templates to niche SaaS and content assets.',
                'keywords' => ['online business ideas', 'developer business ideas', 'productized services', 'SaaS ideas', 'online income'],
                'tags' => ['Online Business', 'Developer Business', 'Productized Services'],
                'image' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Business planning session with laptop and notes',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'matching business ideas with builder strengths and distribution',
            ],
            [
                'category' => 'Business',
                'title' => 'Affiliate Content That Builds Trust Instead of Chasing Clicks',
                'slug' => 'affiliate-content-builds-trust',
                'seo_title' => 'Affiliate Content That Builds Trust Instead of Chasing Clicks',
                'excerpt' => 'A trust-first affiliate content framework for reviews, comparisons, tutorials, and disclosure.',
                'meta_description' => 'Learn how to create affiliate content that builds trust with honest disclosure, useful comparisons, tutorials, and SEO-friendly structure.',
                'keywords' => ['affiliate content', 'affiliate marketing SEO', 'trust-based affiliate marketing', 'monetization friendly content', 'blog monetization'],
                'tags' => ['Affiliate', 'SEO', 'Monetization'],
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80',
                'image_alt' => 'Analytics charts for affiliate content performance',
                'image_credit' => 'Photo source: Unsplash',
                'angle' => 'earning ethically by helping readers make better choices',
            ],
        ];
    }

    private function tools(): array
    {
        return [
            ['name' => 'Laravel VPS Hosting', 'category' => 'Hosting', 'description' => 'A VPS-style hosting option for Laravel blogs and small SaaS projects that need control, SSL, queues, and backups.', 'affiliate_url' => null, 'logo' => null, 'is_featured' => true],
            ['name' => 'Managed Laravel Platform', 'category' => 'Hosting', 'description' => 'A simpler hosting path when you want deployment convenience, monitoring, and less server maintenance.', 'affiliate_url' => null, 'logo' => null, 'is_featured' => false],
            ['name' => 'ChatGPT', 'category' => 'AI Tools', 'description' => 'Useful for outlines, research prompts, editing passes, customer replies, and structured business workflows.', 'affiliate_url' => null, 'logo' => null, 'is_featured' => true],
            ['name' => 'AI Automation Builder', 'category' => 'AI Tools', 'description' => 'A no-code or low-code automation tool for connecting forms, email, spreadsheets, and AI-assisted operations.', 'affiliate_url' => null, 'logo' => null, 'is_featured' => true],
            ['name' => 'Code Editor Setup', 'category' => 'Developer Tools', 'description' => 'A focused editor setup with linting, formatting, terminal workflows, and project search for faster shipping.', 'affiliate_url' => null, 'logo' => null, 'is_featured' => true],
            ['name' => 'Cash Flow Spreadsheet', 'category' => 'Finance Tools', 'description' => 'A simple cash-flow tracker for freelancers to plan income, taxes, subscriptions, and savings buffers.', 'affiliate_url' => null, 'logo' => null, 'is_featured' => false],
        ];
    }

    private function faqs(array $data): array
    {
        return [
            [
                'question' => 'Who is this guide for?',
                'answer' => "This guide is for builders, freelancers, developers, and small business owners interested in {$data['category']} without unnecessary complexity.",
            ],
            [
                'question' => 'Is this beginner-friendly?',
                'answer' => 'Yes. The goal is to explain practical first steps, risks, and tradeoffs in plain language.',
            ],
        ];
    }

    private function content(string $title, string $category, string $angle): string
    {
        $specific = match ($category) {
            'Laravel' => [
                'example' => 'For a Laravel project, I normally start with routes, Blade views, database indexes, queues, and deployment checks before I touch fancy features.',
                'tooling' => "```bash\nphp artisan route:list\nphp artisan config:cache\nphp artisan queue:work --tries=3\n```",
                'mistake' => 'The mistake I see often is shipping a Laravel app with no queue plan, no backup plan, and no production cache strategy. It works on the first day, then becomes fragile when real users arrive.',
            ],
            'AI' => [
                'example' => 'For AI work, I use ChatGPT for drafting and edge cases, Claude for long document reasoning, Perplexity for research trails, and Cursor when I want AI close to the codebase.',
                'tooling' => 'A real workflow is simple: ask Perplexity for sources, use Claude to compare options, use ChatGPT to draft the checklist, then verify everything manually before publishing or building.',
                'mistake' => 'The mistake is letting AI make decisions without context. AI is useful when you bring judgment, constraints, and examples from the real business.',
            ],
            'Finance' => [
                'example' => 'For a freelancer making 12,000 MAD one month and 4,000 MAD the next, the system matters more than motivation: separate tax money, operating money, emergency savings, and personal spending.',
                'tooling' => 'A practical setup is 50% essentials, 20% tax and emergency buffer, 20% business reinvestment, and 10% flexible spending until your income becomes predictable.',
                'mistake' => 'The mistake is treating every paid invoice like profit. A good month can hide weak cash flow if you forget taxes, subscriptions, hosting, transport, and slow client payments.',
            ],
            'Business' => [
                'example' => 'I have seen developers sell a website when the client actually needed a lead system: landing page, CRM pipeline, WhatsApp follow-up, analytics, and a clean offer.',
                'tooling' => 'A serious small business does not need twenty tools. It needs one clear offer, one page that explains it, one way to capture leads, and one follow-up process that someone actually uses.',
                'mistake' => 'The mistake is copying a startup playbook before proving the offer. Most early businesses need trust, distribution, and delivery discipline before they need complex automation.',
            ],
            default => [
                'example' => 'For a tech setup, I look at uptime, backups, security, device reliability, and how much friction the tool removes from daily work.',
                'tooling' => 'A useful technical decision has a checklist: cost per month, setup time, recovery plan, security risk, and whether it still works when the project grows.',
                'mistake' => 'The mistake is buying the popular tool without asking what job it must do. Good tech feels boring because it quietly reduces mistakes every week.',
            ],
        };

        return collect([
            "Most people do not fail at {$title} because they are lazy. They fail because the system is vague, the advice is too broad, and nobody shows what the work looks like on a normal week.",
            "I care about {$angle} because I have watched small decisions compound inside real projects. When you choose the wrong process, tool, or budget rule, the damage usually appears later: missed deadlines, weak cash flow, slow pages, messy operations, or confused clients.",
            'The problem is not lack of information. The problem is that too much content sounds confident but does not survive contact with real work. You need a simple way to decide what matters, what can wait, and what will create fewer problems next month.',
            '## Start with the real constraint',
            "Before you copy any tactic, write down the constraint. Is it time, cash, trust, technical skill, distribution, or maintenance? For {$category}, this one question changes the answer. {$specific['example']} That is the difference between advice that sounds smart and advice you can use.",
            'A practical example: if you only have five hours this week, do not build a complete system. Build the smallest version that gives you feedback. One checklist, one spreadsheet, one landing page, one workflow, or one deployment improvement is enough if it removes friction.',
            '## Build the workflow before the tool stack',
            "{$specific['tooling']} Tools are useful only when they serve a workflow. I prefer writing the workflow first: trigger, action, review, and next decision. This keeps you from buying software, installing packages, or opening accounts you do not actually need.",
            'When I build for clients, the best results come from boring clarity. Who uses this? What happens first? What happens when something fails? What number tells us it is working? If those answers are missing, the tool choice will not save the project.',
            '## Avoid the expensive version of the mistake',
            "{$specific['mistake']} Be direct with yourself here. If a decision adds monthly cost, maintenance, or operational dependency, it needs to earn its place.",
            'The cheaper mistake is testing manually for a week. The expensive mistake is automating a bad process, signing up for tools too early, or designing a system nobody on the team understands. I would rather see you move slower for seven days than lock yourself into a weak setup for six months.',
            '## What To Do This Week',
            "Pick one part of {$title} and turn it into a one-page operating note. Write the goal, the next action, the tool you will use, the review date, and the number you will check. Keep it visible for one week.",
            'After seven days, ask one honest question: did this reduce confusion or create more of it? If it reduced confusion, improve it. If it created more, simplify it until the next step is obvious.',
            'The best systems I have built were not impressive on day one; they became valuable because someone used them, reviewed them, and kept improving the boring parts.',
            'If you want this built properly, I do exactly this kind of work. Reach out here.',
        ])->implode("\n\n");
    }
}
