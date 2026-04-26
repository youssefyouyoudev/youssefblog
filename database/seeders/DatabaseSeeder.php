<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
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
                    'published_at' => now()->subDays(15 - $index)->setTime(9, 0),
                    'seo_title' => $data['seo_title'],
                    'meta_description' => $data['meta_description'],
                    'keywords' => $data['keywords'],
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
    }

    private function posts(): array
    {
        return [
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

    private function content(string $title, string $category, string $angle): string
    {
        return collect([
            '## Why this matters in 2026',
            "{$title} is important because builders now have more tools, more competition, and more noise than ever. The advantage is not doing everything. The advantage is choosing a practical system and repeating it long enough to get useful feedback.",
            "For {$category} readers, the key angle is {$angle}. That means the guide should help you make clearer decisions, avoid expensive mistakes, and build a routine that still works on a busy week.",
            '## The simple starting point',
            'Start with one measurable outcome. If the topic is money, track one number weekly. If it is software, track speed, reliability, or time saved. If it is business, track leads, published assets, and useful conversations.',
            'Do not build a complicated dashboard on day one. A simple note, spreadsheet, or checklist is enough until the habit is stable. Complexity should arrive only when it removes friction.',
            '## A practical workflow',
            'First, define the job you need the system to do. Second, pick the smallest tool stack that can do that job. Third, schedule a weekly review so you can remove what is not helping.',
            'This workflow creates internal linking opportunities too. A finance reader can move into fintech tools, an AI reader can move into ChatGPT workflows, and a Laravel reader can move into deployment or SEO guides.',
            '## Common mistakes to avoid',
            "Avoid chasing tools before you understand the problem. Avoid copying someone else's setup without adapting it to your market, budget, and skill level. Avoid advice that promises fast results without explaining risk, tradeoffs, or maintenance.",
            'The better path is boring in the best way: clear inputs, simple execution, honest review, and gradual improvement.',
            '## Next step',
            'Choose one idea from this guide and turn it into a checklist you can use this week. If it saves time, reduces confusion, or improves decision quality, keep it. If not, simplify it and try again.',
        ])->implode("\n\n");
    }
}
