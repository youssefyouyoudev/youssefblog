<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateContentSchedule extends Command
{
    protected $signature = 'content:generate-schedule {--start= : First schedule date in YYYY-MM-DD format} {--dry-run : Build the calendar and report without saving posts}';

    protected $description = 'Generate a 60-day editorial schedule of 300 long-form draft/scheduled posts.';

    private const TIMEZONE = 'Africa/Casablanca';

    private const DAILY_TIMES = ['08:00', '11:00', '14:00', '17:00', '20:00'];

    private const IMAGE_POOL = [
        'Laravel' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1400&q=80',
        'SaaS' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1400&q=80',
        'AI Tools' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&q=80',
        'Finance' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1400&q=80',
        'Morocco Business' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80',
        'Freelancing' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80',
        'Web Development' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=80',
        'SEO' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80',
        'Tools & Productivity' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1400&q=80',
        'Business Systems' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1400&q=80',
    ];

    public function handle(): int
    {
        $startDate = $this->startDate();
        $topics = $this->scheduledTopics($this->topics(), $startDate);
        $this->validateSchedule($topics);
        foreach ($topics as $topic) {
            $this->validateContent($topic, $this->content($topic));
        }

        if ($this->option('dry-run')) {
            $this->writeReports($topics, collect(), collect());
            $this->info('Dry run complete. Reports were created without saving posts.');

            return self::SUCCESS;
        }

        $postColumns = array_flip(Schema::getColumnListing((new Post)->getTable()));
        $author = $this->author();
        $categories = $this->categories($topics);
        $tags = $this->tags($topics);
        $existingSlugs = Post::pluck('slug')->flip();
        $created = 0;
        $updated = 0;

        foreach ($topics as $topic) {
            $content = $this->content($topic);
            $this->validateContent($topic, $content);
            $slug = $this->safeSlug($topic['base_slug'], $topic['title'], $existingSlugs);
            $topic['slug'] = $slug;

            $payload = [
                'user_id' => $author->id,
                'category_id' => $categories[$topic['category']]->id,
                'title' => $topic['title'],
                'slug' => $slug,
                'excerpt' => $topic['excerpt'],
                'content' => $content,
                'featured_image' => $topic['image'],
                'featured_image_alt' => $topic['image_alt'],
                'image_credit' => 'Photo source: Unsplash. Unsplash license allows free use for commercial and non-commercial projects.',
                'status' => 'scheduled',
                'published_at' => $topic['scheduled_at'],
                'meta_title' => $topic['meta_title'],
                'seo_title' => $topic['meta_title'],
                'meta_description' => $topic['meta_description'],
                'keywords' => $topic['keywords'],
                'faqs' => $this->faqs($topic),
                'canonical_url' => null,
                'og_image' => $topic['image'],
                'reading_time' => $this->readingTime($content),
                'views' => 0,
                'is_featured' => in_array($topic['cluster'], ['Laravel', 'Finance', 'SaaS', 'AI Tools', 'Morocco Business'], true) && $topic['cluster_index'] === 1,
                'last_updated_at' => null,
                'schema_type' => 'BlogPosting',
            ];

            $post = Post::updateOrCreate(
                ['slug' => $slug],
                array_intersect_key($payload, $postColumns),
            );

            $post->wasRecentlyCreated ? $created++ : $updated++;

            if (Schema::hasTable('post_tag')) {
                $post->tags()->sync(
                    collect($topic['tags'])
                        ->map(fn (string $tag): int => $tags[$tag]->id)
                        ->all(),
                );
            }
        }

        $this->writeReports($topics, collect($categories), collect($tags));
        $this->info("Content schedule complete: {$created} created, {$updated} updated.");
        $this->line('Status: scheduled with future published_at dates for admin review.');
        $this->line('Report: storage/app/content-schedule-report.md');
        $this->line('CSV: storage/app/content-schedule.csv');

        return self::SUCCESS;
    }

    private function startDate(): CarbonImmutable
    {
        $option = $this->option('start');

        return $option
            ? CarbonImmutable::parse($option, self::TIMEZONE)->startOfDay()
            : CarbonImmutable::tomorrow(self::TIMEZONE)->startOfDay();
    }

    private function author(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@youssefyouyou.com'],
            [
                'name' => 'Youssef Youyou',
                'password' => Hash::make(Str::random(32)),
                'role' => 'admin',
            ],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $topics
     * @return array<string, Category>
     */
    private function categories(array $topics): array
    {
        $descriptions = [
            'Laravel' => 'Beginner-to-production Laravel tutorials covering routes, controllers, Blade, Eloquent, testing, security, SEO, and deployment.',
            'SaaS' => 'Practical SaaS and micro SaaS guides for validation, architecture, onboarding, dashboards, billing, and retention.',
            'AI Tools' => 'Useful AI tools, automation workflows, prompt systems, privacy notes, and business implementation guides.',
            'Finance' => 'Educational finance basics for beginners, freelancers, developers, and small business owners.',
            'Morocco Business' => 'Digital business systems for Moroccan SMEs, schools, clinics, agencies, and service companies.',
            'Freelancing' => 'Freelance developer pricing, client acquisition, proposals, onboarding, communication, and agency systems.',
            'Web Development' => 'Conversion-focused websites, deployment, performance, client-ready builds, and professional web systems.',
            'SEO' => 'SEO, content marketing, technical SEO, internal linking, schema, affiliate SEO, and content refresh systems.',
            'Tools & Productivity' => 'Developer tools, VPS workflows, Cloudflare, monitoring, documentation, and productivity systems.',
            'Business Systems' => 'Case-study style guides for CRM, inventory, invoicing, school systems, and operational dashboards.',
        ];

        return collect($topics)
            ->pluck('category')
            ->unique()
            ->mapWithKeys(fn (string $name): array => [
                $name => Category::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'description' => $descriptions[$name] ?? "Practical {$name} guides for developers and digital business owners.",
                        'seo_title' => "{$name} Guides | Youssef Blog",
                        'meta_description' => Str::limit($descriptions[$name] ?? "Practical {$name} guides for developers and digital business owners.", 155, ''),
                    ],
                ),
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $topics
     * @return array<string, Tag>
     */
    private function tags(array $topics): array
    {
        return collect($topics)
            ->flatMap(fn (array $topic): array => $topic['tags'])
            ->unique()
            ->mapWithKeys(fn (string $tag): array => [
                $tag => Tag::updateOrCreate(['slug' => Str::slug($tag)], ['name' => $tag]),
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $topics
     * @return array<int, array<string, mixed>>
     */
    private function scheduledTopics(array $topics, CarbonImmutable $startDate): array
    {
        return collect($topics)
            ->values()
            ->map(function (array $topic, int $index) use ($startDate): array {
                [$hour, $minute] = explode(':', self::DAILY_TIMES[$index % 5]);
                $topic['day'] = intdiv($index, 5) + 1;
                $topic['scheduled_at'] = $startDate
                    ->addDays(intdiv($index, 5))
                    ->setTime((int) $hour, (int) $minute);
                $topic['base_slug'] = Str::slug($topic['slug'] ?? $topic['title']);
                $topic['slug'] = $topic['base_slug'];
                $topic['meta_title'] = Str::limit($topic['title'].' | Youssef Blog', 68, '');
                $topic['meta_description'] = $this->metaDescription($topic);
                $topic['excerpt'] = $this->excerpt($topic);
                $topic['keywords'] = array_values(array_unique([$topic['keyword'], ...$topic['tags']]));
                $topic['image'] = self::IMAGE_POOL[$topic['category']] ?? self::IMAGE_POOL['Business Systems'];
                $topic['image_alt'] = $topic['image_alt'] ?? 'Workspace representing '.$topic['title'];

                return $topic;
            })
            ->all();
    }

    private function safeSlug(string $baseSlug, string $title, \Illuminate\Support\Collection $reservedSlugs): string
    {
        $existing = Post::where('slug', $baseSlug)->first(['title', 'slug']);

        if ($existing && $existing->title === $title) {
            $reservedSlugs[$baseSlug] = true;

            return $baseSlug;
        }

        if (! $reservedSlugs->has($baseSlug) && ! $existing) {
            $reservedSlugs[$baseSlug] = true;

            return $baseSlug;
        }

        $slug = $baseSlug.'-editorial-schedule';
        $counter = 2;

        while ($reservedSlugs->has($slug)) {
            $candidate = Post::where('slug', $slug)->first(['title', 'slug']);

            if ($candidate && $candidate->title === $title) {
                return $slug;
            }

            $slug = $baseSlug.'-editorial-schedule-'.$counter;
            $counter++;
        }

        $candidate = Post::where('slug', $slug)->first(['title', 'slug']);

        if ($candidate && $candidate->title !== $title) {
            return $this->safeSlug($slug, $title, $reservedSlugs);
        }

        $reservedSlugs[$slug] = true;

        return $slug;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function topics(): array
    {
        return [
            ...$this->laravelTopics(),
            ...$this->financeTopics(),
            ...$this->generatedTopics('SaaS', 'SaaS', 'micro SaaS Morocco', [
                'SaaS Ideas in Morocco for Practical Founders',
                'Laravel SaaS Architecture for a Small Product',
                'How to Price a SaaS Product Without Guessing',
                'Subscription Models Explained for Beginners',
                'SaaS Onboarding That Helps Users Win Early',
                'Dashboard Design for SaaS Products',
                'Billing Workflows for Laravel SaaS Apps',
                'User Roles and Permissions in SaaS Products',
                'CRM SaaS Ideas for Moroccan Businesses',
                'School Management SaaS Product Roadmap',
                'Invoice SaaS for Freelancers and SMEs',
                'Inventory SaaS for Small Retailers',
                'Appointment SaaS for Clinics and Service Teams',
                'Analytics SaaS for Non-Technical Owners',
                'Building a SaaS MVP Without Overbuilding',
                'Validating a SaaS Idea Before Writing Code',
                'Landing Pages for Micro SaaS Products',
                'SaaS SEO for Early-Stage Products',
                'Support Systems for Small SaaS Teams',
                'Retention Basics for SaaS Founders',
                'Multi-Tenant SaaS Explained Simply',
                'Single-Tenant SaaS When It Makes More Sense',
                'SaaS Admin Panels That Save Support Time',
                'Feature Flags for Small SaaS Products',
                'SaaS Email Notifications That Users Understand',
                'Data Export Features Every SaaS Needs',
                'Trial vs Demo for Moroccan SaaS Sales',
                'Building SaaS Documentation for Beginners',
                'SaaS Roadmaps Without Fake Certainty',
                'Customer Feedback Loops for Micro SaaS',
                'SaaS Metrics Beginners Should Understand Carefully',
                'Churn Reasons Small SaaS Teams Can Actually Fix',
                'SaaS Security Basics for Client Trust',
                'SaaS Backup Strategy for Small Teams',
                'SaaS Product Pages That Explain Value Clearly',
                'Micro SaaS for Agencies and Consultants',
                'SaaS Launch Checklist for Solo Developers',
                'SaaS Maintenance Plans After Launch',
                'SaaS Integrations With WhatsApp and Email',
                'Choosing the First SaaS Feature Set',
            ]),
            ...$this->generatedTopics('AI Tools', 'AI Tools', 'AI tools small business Morocco', [
                'AI Tools for Small Businesses Without Hype',
                'ChatGPT Workflows for Daily Business Tasks',
                'AI for Content Planning Without Losing Your Voice',
                'AI for Customer Support Drafts and Knowledge Bases',
                'AI Tools for Freelancers Managing Client Work',
                'AI Agents Explained Ethically for Beginners',
                'Automation Without Spam or Annoying Customers',
                'Prompt Engineering Basics for Useful Outputs',
                'AI for Laravel Developers in Real Projects',
                'AI Dashboards for Business Owners',
                'AI Content Review Checklist Before Publishing',
                'AI Productivity Systems for Busy Freelancers',
                'AI for Lead Research Without Crossing Privacy Lines',
                'AI Limitations Every Business Owner Should Know',
                'AI Safety and Privacy for Small Teams',
                'AI Meeting Notes and Follow-Up Workflows',
                'AI Email Drafting for Client Communication',
                'AI Research Workflows With Human Verification',
                'AI Tools for Bilingual French and Arabic Content',
                'AI for SOPs and Internal Documentation',
                'AI Automation for Invoice Reminders',
                'AI for Social Media Planning Carefully',
                'AI for Proposal Drafting Without Sounding Generic',
                'AI for FAQ Pages and Support Centers',
                'AI Tool Evaluation Before Paying Monthly',
                'AI Workflows for Agencies and Freelancers',
                'AI-Assisted Coding Review for Beginners',
                'AI for CRM Notes and Sales Follow-Up',
                'AI for School Administration Tasks',
                'AI for Local Service Businesses in Morocco',
                'AI Prompt Libraries for Business Teams',
                'AI Workflow Mistakes Beginners Make',
                'AI Data Privacy Checklist for Client Projects',
                'AI and Human Review in Content Operations',
                'AI Automation Roadmap for the First 30 Days',
            ]),
            ...$this->generatedTopics('Morocco Business', 'Morocco Business', 'digital business Morocco', [
                'Websites for Moroccan SMEs That Need More Leads',
                'Local SEO Morocco for Service Businesses',
                'Digital Transformation for Small Moroccan Companies',
                'CRM for Moroccan Businesses Explained Simply',
                'Invoicing Systems for Moroccan SMEs',
                'Stock Management Systems for Small Shops',
                'School Management Systems in Morocco',
                'Clinic Management Systems for Better Operations',
                'Restaurant Websites That Bring Real Reservations',
                'Real Estate Websites for Moroccan Agencies',
                'Service Business Websites With WhatsApp Follow-Up',
                'Pricing Websites in Morocco Without Confusing Clients',
                'WhatsApp Business Workflows for SMEs',
                'Online Trust Signals for Moroccan Companies',
                'Bilingual French and Arabic Website Planning',
                'Digital Forms for Moroccan Service Businesses',
                'Appointment Systems for Local Businesses',
                'Payment Tracking for Small Moroccan Teams',
                'Customer Follow-Up Systems for SMEs',
                'Website Maintenance for Moroccan Businesses',
                'Google Business Profile Basics for Morocco',
                'Lead Management for Training Centers',
                'Dashboards for Business Owners Who Hate Spreadsheets',
                'Simple ERP Ideas for Moroccan SMEs',
                'Inventory and Sales Reports for Retailers',
                'Client Portals for Agencies and Consultants',
                'Digital Receipts and Records for Small Teams',
                'Online Booking for Clinics and Beauty Salons',
                'Trust-First Website Copy for Moroccan Buyers',
                'Content Strategy for Local Moroccan Brands',
                'Digital Operations Checklist for SMEs',
                'How Moroccan SMEs Can Prepare for Automation',
                'Choosing Software for a Small Moroccan Business',
                'Common Website Mistakes Moroccan SMEs Make',
                'From Spreadsheet to Web App for Local Businesses',
            ]),
            ...$this->generatedTopics('Freelancing', 'Freelancing', 'freelance developer Morocco pricing', [
                'Getting Your First Web Development Client',
                'Pricing Websites as a Freelance Developer',
                'Writing Proposals That Explain Value Clearly',
                'Project Discovery Questions Before Coding',
                'Client Onboarding for Freelance Developers',
                'Contract Basics for Web Projects',
                'Avoiding Scope Creep Without Being Difficult',
                'Portfolio Improvements That Build Trust',
                'Writing Case Studies for Client Projects',
                'LinkedIn Outreach for Developers Without Spam',
                'Cold Email for Freelancers Done Ethically',
                'Web Agency Systems Before Hiring',
                'Maintenance Plans for Client Websites',
                'Monthly Retainers for Freelance Developers',
                'Client Communication Systems That Prevent Confusion',
                'Freelance Project Timelines That Stay Realistic',
                'How to Present Website Audits to Prospects',
                'Discovery Calls for Moroccan Business Clients',
                'Freelance Invoicing Habits That Reduce Stress',
                'How to Say No to Bad-Fit Projects',
                'Turning Freelance Services Into Packages',
                'Building Referral Systems for Developers',
                'Managing Revisions in Website Projects',
                'Client Handover Checklist for Developers',
                'Freelance Positioning for Laravel Developers',
                'How Agencies Can Document Delivery Workflows',
                'Support Agreements After Website Launch',
                'How to Build Trust Before a Sales Call',
                'Freelance Pricing Mistakes Beginners Make',
                'Project Retrospectives for Better Client Work',
            ]),
            ...$this->generatedTopics('SEO', 'SEO', 'SEO basics for beginners', [
                'SEO Basics for Beginners Building a Blog',
                'Keyword Research Without Overcomplicating It',
                'Content Clusters Explained for Practical Blogs',
                'Internal Linking Strategy for New Websites',
                'Technical SEO Checklist for Small Sites',
                'Sitemap Basics and Common Mistakes',
                'Robots.txt Explained for Beginners',
                'Schema Markup for Blogs and Business Websites',
                'Content Refresh Strategy for Old Posts',
                'Affiliate SEO Without Losing Trust',
                'AdSense Readiness Checklist for Blogs',
                'Blog Monetization Without Weak Content',
                'Local SEO for Moroccan Service Businesses',
                'SEO for Laravel Websites',
                'SEO Mistakes That Make Good Content Invisible',
                'Meta Titles and Descriptions That Help Readers',
                'Image SEO for Beginner Bloggers',
                'Pagination and Canonical URLs Explained',
                'How to Plan a 90-Day Content Calendar',
                'Topic Authority for Small Niche Blogs',
                'SEO Reporting for Non-Technical Clients',
                'Search Intent Explained With Examples',
                'Blog Category Pages That Are Worth Indexing',
                'Thin Content Problems and How to Fix Them',
                'Editorial Standards for SEO Content',
            ]),
            ...$this->generatedTopics('Tools & Productivity', 'Tools & Productivity', 'developer productivity tools', [
                'VPS Hosting Basics for Beginners',
                'Nginx Basics for Laravel Developers',
                'Cloudflare Setup for Small Websites',
                'Git Deployment Workflow for Client Projects',
                'Hostinger for Beginner Web Projects',
                'Ubuntu Server Setup for Laravel Apps',
                'Backup Strategy for Small Websites',
                'Monitoring Basics for Laravel Apps',
                'Analytics Setup Without Drowning in Data',
                'Productivity Tools for Solo Developers',
                'Coding Tools That Actually Save Time',
                'Project Management for Freelancers',
                'Documentation Habits for Small Teams',
                'Notion Workflows for Client Projects',
                'Google Sheets Workflows for Business Dashboards',
                'Developer Laptop Setup for Laravel Work',
                'Terminal Habits for Beginner Developers',
                'VS Code Setup for Laravel and Writing',
                'Password Management for Freelancers',
                'File Organization for Client Projects',
                'Uptime Checks for Business Websites',
                'Error Monitoring Basics for Web Apps',
                'Staging Environments for Small Projects',
                'Database Backup Testing for Beginners',
                'Cloud Storage Workflows for Agencies',
                'Content Calendar Tools for Bloggers',
                'Time Blocking for Developers With Client Work',
                'Personal Knowledge Base for Builders',
                'Automation Tools for Repetitive Admin',
                'Checklists for Website Launches',
                'Developer Desk Setup Without Overspending',
                'Managing Browser Tabs and Research Notes',
                'Weekly Review System for Freelancers',
                'Choosing Tools Without Subscription Creep',
                'Deployment Documentation for Client Handover',
            ]),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function laravelTopics(): array
    {
        $titles = [
            'What Is Laravel and Why Beginners Should Learn It',
            'Installing Laravel Locally Step by Step',
            'Laravel Project Structure Explained for Beginners',
            'Laravel Routes Explained From Zero',
            'Controllers in Laravel Explained With Examples',
            'Blade Templates From Beginner to Practical Use',
            'Laravel Layouts and Components',
            'Laravel Migrations Explained',
            'Laravel Models and Eloquent Basics',
            'Laravel Factories and Seeders',
            'CRUD App in Laravel From Scratch',
            'Validation and Form Requests',
            'Authentication With Laravel Breeze',
            'Authorization, Gates, and Policies',
            'File Uploads and Storage',
            'Laravel Relationships Explained',
            'Query Scopes and Accessors',
            'Pagination, Search, and Filters',
            'Laravel Mail and Notifications',
            'Laravel Queues and Jobs',
            'Laravel Service Classes and Clean Architecture',
            'Repositories: When to Use and When to Avoid',
            'Laravel Events and Listeners',
            'Laravel API Development',
            'API Authentication With Sanctum',
            'Laravel Rate Limiting',
            'Laravel Caching',
            'Laravel Task Scheduling',
            'Laravel Observers',
            'Laravel Custom Artisan Commands',
            'Laravel Testing for Beginners',
            'Pest vs PHPUnit in Laravel',
            'Feature Tests for CRUD',
            'Authentication Tests',
            'API Tests',
            'Database Tests',
            'Mocking Services',
            'Testing File Uploads',
            'Testing Queues and Mail',
            'Building a Test Strategy for Laravel Apps',
            'Laravel Security Checklist',
            'Preventing XSS and SQL Injection in Laravel',
            'CSRF and Session Security',
            'Laravel SEO Basics',
            'Meta Tags and Open Graph in Laravel',
            'Sitemap and Robots.txt in Laravel',
            'Structured Data in Laravel',
            'Performance Optimization for Laravel',
            'Laravel Logging and Error Monitoring',
            'Laravel Backup Strategy',
            'Deploy Laravel on Ubuntu VPS',
            'Nginx Configuration for Laravel',
            'PHP-FPM Setup for Laravel',
            'MySQL Production Setup',
            'SSL With Certbot',
            'Deploy Laravel With Git Pull',
            'Laravel Env and Config Cache',
            'Fix Common Laravel 500 Errors',
            'Laravel Queue Worker in Production',
            'Laravel Deployment Checklist',
        ];

        return collect($titles)->map(fn (string $title, int $index): array => $this->topic(
            title: $title,
            category: 'Laravel',
            cluster: 'Laravel',
            clusterIndex: $index + 1,
            keyword: Str::of($title)->lower()->replaceMatches('/[^a-z0-9 ]/', '')->squish()->toString(),
            tags: ['Laravel', 'PHP', 'Web Development', 'Beginner Laravel', $index >= 30 && $index < 40 ? 'Testing' : 'Production'],
        ))->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function financeTopics(): array
    {
        $titles = [
            'Personal Finance for Absolute Beginners',
            'Budgeting From Zero',
            'Tracking Expenses',
            'Emergency Fund Basics',
            'Saving Money on Low Income',
            'Understanding Cash Flow',
            'Bank Accounts Explained',
            'Freelance Income Management',
            'Separating Personal and Business Money',
            'Invoicing and Payment Tracking',
            'Taxes Basics for Freelancers',
            'Debt Basics',
            'Good Debt vs Bad Debt',
            'How to Avoid Lifestyle Inflation',
            'Building a Monthly Money Routine',
            'Beginner Investing Concepts',
            'Risk and Reward Explained',
            'Compound Growth Explained',
            'Index Funds Explained Generally',
            'Real Estate Basics',
            'Business Income vs Salary',
            'Side Hustle Budgeting',
            'Pricing Your Freelance Work',
            'Building a Simple Financial Dashboard',
            'Saving for Equipment',
            'Managing Irregular Income',
            'Financial Mistakes Beginners Make',
            'How to Plan a One-Year Financial Goal',
            'How to Build Wealth Slowly',
            'How to Think About Your First 100k',
            'How Millionaires Usually Build Wealth Slowly',
            'Money Mindset Without Fake Motivation',
            'Financial Planning for Developers',
            'Budgeting for Moroccan Freelancers',
            'Moroccan Banking Basics for Freelancers',
            'Cash Flow for Small Businesses',
            'Profit vs Revenue Explained',
            'How to Reinvest in Your Skills',
            'How to Build Multiple Income Streams Safely',
            'Beginner Financial Roadmap',
        ];

        return collect($titles)->map(fn (string $title, int $index): array => $this->topic(
            title: $title,
            category: 'Finance',
            cluster: 'Finance',
            clusterIndex: $index + 1,
            keyword: Str::of($title)->lower()->replaceMatches('/[^a-z0-9 ]/', '')->squish()->toString(),
            tags: ['Finance', 'Freelancers', 'Money Habits', 'Beginner Finance', $index >= 33 ? 'Morocco' : 'Education'],
        ))->all();
    }

    /**
     * @param  array<int, string>  $titles
     * @return array<int, array<string, mixed>>
     */
    private function generatedTopics(string $category, string $cluster, string $keyword, array $titles): array
    {
        return collect($titles)->map(fn (string $title, int $index): array => $this->topic(
            title: $title,
            category: $category,
            cluster: $cluster,
            clusterIndex: $index + 1,
            keyword: $keyword,
            tags: array_values(array_unique([$category, $cluster, 'Beginner Guide', 'Digital Business', 'Morocco'])),
        ))->all();
    }

    /**
     * @param  array<int, string>  $tags
     * @return array<string, mixed>
     */
    private function topic(string $title, string $category, string $cluster, int $clusterIndex, string $keyword, array $tags): array
    {
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => $category,
            'cluster' => $cluster,
            'cluster_index' => $clusterIndex,
            'keyword' => $keyword,
            'tags' => array_slice(array_values(array_unique([...$tags, $cluster.' Series', 'Practical Systems'])), 0, 8),
        ];
    }

    private function metaDescription(array $topic): string
    {
        $description = match ($topic['category']) {
            'Finance' => "Educational guide to {$topic['title']} for beginners, freelancers, and practical money habits without hype or guarantees.",
            'Laravel' => "Beginner-friendly Laravel guide to {$topic['title']} with examples, mistakes, checklists, and production notes.",
            default => "Practical beginner guide to {$topic['title']} for developers, freelancers, Moroccan SMEs, and digital business builders.",
        };

        return Str::of($description)->squish()->limit(155, '')->beforeLast(' ')->toString();
    }

    private function excerpt(array $topic): string
    {
        return Str::of("A detailed beginner-friendly guide to {$topic['title']}, with practical steps, examples, common mistakes, a checklist, FAQs, and internal link suggestions for deeper learning.")
            ->limit(190, '')
            ->beforeLast(' ')
            ->toString();
    }

    private function content(array $topic): string
    {
        $isLaravel = $topic['category'] === 'Laravel';
        $isFinance = $topic['category'] === 'Finance';
        $related = $this->relatedLinks($topic);
        $code = $isLaravel ? $this->laravelCodeExamples($topic) : '';
        $financeDisclaimer = $isFinance
            ? "\n\n> This guide is educational only and is not personal financial advice. Always make decisions based on your own situation or speak with a qualified professional.\n"
            : '';
        $table = $isFinance ? $this->financeTable() : $this->planningTable($topic);
        $voice = $this->voiceParagraph($topic);

        return <<<MARKDOWN
{$financeDisclaimer}
{$voice}

This guide is written for beginners who want practical understanding, not noise. The goal is to make {$topic['title']} clear enough that you can take the next responsible step, ask better questions, and avoid common mistakes that make projects or money decisions harder than they need to be.

The topic also fits the wider Youssef Blog editorial strategy: Laravel, SaaS, AI tools, finance habits, Moroccan business systems, freelancing, SEO, deployment, and practical digital growth. The best version of this article should connect naturally to related guides so readers can keep learning without feeling pushed around.

## Table of Contents

- Why this topic matters
- The beginner-friendly explanation
- A practical workflow
- Examples you can adapt
- Common mistakes
- Checklist
- Internal links to review
- FAQ
- Final summary

## Why This Topic Matters

{$topic['title']} matters because beginners often meet the topic at the wrong moment. They hear the advanced version first, then feel behind before they have even learned the basics. A Laravel beginner might hear about queues, policies, service containers, and deployment before understanding routes. A freelancer might hear about retainers before learning discovery calls. A business owner might hear about AI agents before documenting the process they want to automate.

The better path is calmer. Start with the purpose. Ask what problem the topic solves. Ask who uses it. Ask what happens if you ignore it. Then build from the smallest useful version toward a more complete system.

For Moroccan SMEs and freelancers, this matters even more because many projects are built with limited time, limited budgets, and real operational pressure. A website, CRM, SaaS dashboard, content system, or finance routine should reduce confusion. If it creates more confusion, it is not mature yet.

## The Beginner-Friendly Explanation

Think of {$topic['title']} as a practical system, not just an idea. A system has inputs, steps, decisions, outputs, and review points. When you see the system, you can improve it. When you only see scattered tasks, everything feels urgent.

For example, a business website is not only a design. It is a trust path: visitor arrives, understands the offer, sees proof, chooses a next step, and contacts the business. A Laravel feature is not only code. It is a user action, a route, validation, database changes, permissions, feedback messages, and maintenance. A finance habit is not only motivation. It is tracking, limits, review, and realistic decisions.

This beginner explanation is intentionally simple because simple foundations survive. Once you understand the core workflow, advanced tools become easier to judge.

## A Practical Workflow

Use this workflow before you build, buy, automate, or publish anything related to {$topic['title']}:

1. Write the main problem in one sentence.
2. Identify who experiences the problem most often.
3. Write the current manual process step by step.
4. Mark the step that causes the most delay or confusion.
5. Choose one improvement that can be tested this week.
6. Define what success looks like without exaggerating.
7. Review the result and improve the next version.

This sequence protects you from overbuilding. It also makes the work easier to explain to clients, teammates, or future you. If the process cannot be explained simply, it is not ready for a complex implementation.

{$table}

## Practical Example

Imagine a small Moroccan training center trying to improve operations. Leads arrive from WhatsApp, Instagram, referrals, and walk-ins. Staff write notes in different places. Some prospects receive follow-up, others disappear. The owner wants "automation," but the real problem is not automation yet. The real problem is that lead status is unclear.

The practical first step is not a massive platform. It could be a simple CRM workflow: name, contact, source, course interest, next follow-up date, status, and notes. Once that works, the team can add reminders, reports, email templates, and eventually a Laravel dashboard.

This same thinking applies to {$topic['title']}. Look for the operational bottleneck first. Then choose the smallest serious improvement.

## Step-by-Step Implementation Notes

Start by documenting the current state. Do not skip this because documentation feels boring. The current state shows what is actually happening, not what the plan says should happen.

Next, define the first version. A first version should be clear, useful, and maintainable. It does not need every feature. It needs the few pieces that make the core workflow better.

Then test with a real scenario. Use one client, one page, one feature, one week of tracking, one small campaign, or one internal process. The test should produce feedback, not just a feeling.

Finally, improve based on what happened. If people ignored the tool, ask why. If the workflow broke, find the step. If the content did not rank, check search intent, internal links, and quality. If a Laravel feature failed, check validation, logs, permissions, and assumptions.

{$code}

## How To Adapt This To Your Situation

The same advice will not fit every reader. A beginner developer, a Moroccan SME owner, a freelancer, and a small agency may all care about {$topic['title']}, but each one has a different constraint. The developer may need code clarity. The business owner may need trust and follow-up. The freelancer may need pricing discipline. The agency may need repeatable delivery.

Start by naming your constraint honestly. If your constraint is budget, choose a smaller first version. If your constraint is time, choose a workflow you can test quickly. If your constraint is trust, improve proof, communication, and documentation. If your constraint is technical skill, keep the implementation boring until the basics are stable.

For a local Moroccan business, adaptation often means respecting how the team already works. If everyone uses WhatsApp, do not pretend that a new dashboard will replace communication overnight. Instead, use the dashboard to keep records clean and use WhatsApp for reminders or human follow-up. If the team uses spreadsheets, import or export cleanly. If the owner speaks French and Arabic with customers, the system and content should support that reality where it matters.

For a developer, adaptation means keeping maintainability visible. Do not only ask, "Can I build this?" Ask, "Can I explain this, test this, deploy this, and fix this three months later?" That question changes the architecture. It pushes you toward clear names, smaller classes, useful tests, readable database fields, and documentation that someone can actually follow.

## Practical Exercise

Take fifteen minutes and write a one-page plan for {$topic['title']}. Do not make it beautiful. Make it useful. Use these prompts:

- What is the main problem?
- Who feels this problem most often?
- What is the current messy process?
- What would a cleaner version look like?
- What is the smallest useful improvement?
- What could go wrong?
- What should be reviewed after one week?

This exercise is simple, but it often reveals the real work. You may discover that the first step is not a new tool, but a better form. You may discover that the website does not need a redesign yet, but the service offer needs clarity. You may discover that a SaaS idea is too broad and should become a small internal workflow first.

Do the exercise twice if you can. The first version usually captures what you think the problem is. The second version is often more honest because you start noticing missing steps, unclear owners, and assumptions you copied from other projects. That second version is where the useful decisions normally appear.

Keep the notes. They become useful later when you compare your original plan with what actually happened in practice, especially when you need to explain a decision to a client, teammate, or future version of yourself.

## Review Before You Go Further

Before investing more time, review the plan with a skeptical mindset. A useful plan should survive basic questions. Can a beginner understand it? Can a client see the value? Can a teammate repeat the process? Can you maintain it without heroic effort? Can it be improved after feedback?

If the answer is no, simplify. Simple is not weak. Simple means the idea is clear enough to carry weight. Many strong systems start as a small page, a good checklist, a reliable form, a simple dashboard, or a short workflow that people actually use.

Also review the language you use. If the explanation depends on buzzwords, the work probably needs more thinking. Good explanations are specific. They name the user, the problem, the constraint, and the next action. This matters for code, finance, content, SaaS, and business operations because vague language hides weak decisions. Clear language exposes them early, when they are cheaper to fix.

## Common Mistakes

- Starting with tools before defining the workflow.
- Copying advanced advice without understanding the beginner version.
- Creating too many features, pages, automations, or habits at once.
- Forgetting maintenance, review, backups, security, or documentation.
- Making claims that sound impressive but are not supported by real work.
- Treating local Moroccan business realities as an afterthought.
- Publishing thin content instead of improving a useful draft.

These mistakes are common because they feel productive in the moment. Overbuilding feels like progress. Buying another tool feels like commitment. Writing a long page without structure feels like content. But the useful work is usually quieter: clarify, test, review, simplify, then improve.

## Beginner Checklist

- Can you explain {$topic['title']} in plain language?
- Do you know who this helps?
- Have you written the current workflow?
- Have you chosen one practical next action?
- Have you listed the risks and constraints?
- Have you checked whether the first version is maintainable?
- Have you planned how to review the result?
- Have you added internal links only where they help the reader?

## Internal Link Suggestions

{$related}

These links should be reviewed when the related posts are published. If a linked post is still scheduled for the future, keep it as an editorial note or add the link after both articles are live.

## FAQ

### Is {$topic['title']} beginner-friendly?

Yes, if you start with the workflow and avoid jumping straight into advanced tools. Beginners need context first, then steps, then examples, then practice.

### Should I use this for every project?

No. Use it when it solves a real problem. Not every business needs the same tool, article, system, or workflow. The best choice depends on the user, budget, maintenance capacity, and desired outcome.

### How do I know the first version is good enough?

A first version is good enough when it helps someone complete the main job with less confusion. It can be simple, but it should not be careless. It needs clear labels, reliable data, and an obvious next step.

### What should I review before publishing or launching?

Review accuracy, usefulness, user flow, links, SEO metadata, mobile experience, performance, security, and whether the page or system makes a realistic promise.

## Final Summary

{$topic['title']} becomes useful when you treat it as a practical system. Start with the problem, write the workflow, build the first serious version, and improve it based on feedback. Avoid hype, avoid fake certainty, and avoid copying advice that does not fit your situation.

For Youssef Blog, this article supports a larger learning path. It can connect to related Laravel, SaaS, AI, finance, Morocco business, freelancing, SEO, and productivity guides as the topic cluster grows.

The next step is simple: apply one part of the checklist, observe what changes, and improve the system based on real feedback rather than assumptions.
MARKDOWN;
    }

    private function voiceParagraph(array $topic): string
    {
        return match ($topic['category']) {
            'Laravel' => "From experience building Laravel-backed websites and dashboards, the biggest beginner mistake is trying to memorize everything instead of understanding the request lifecycle. {$topic['title']} should be learned through small working examples, not only definitions.",
            'Finance' => "Finance can feel intimidating when every explanation jumps to advanced terms. {$topic['title']} should begin with habits, cash flow, risk awareness, and calm decisions before anyone thinks about complex products.",
            'AI Tools' => "AI tools are useful when they support judgment instead of replacing it. {$topic['title']} should be approached with privacy, review, and practical workflow design in mind.",
            default => "The best digital business work is usually practical and specific. {$topic['title']} should help a real person make a clearer decision, improve a workflow, or build something maintainable.",
        };
    }

    private function laravelCodeExamples(array $topic): string
    {
        return <<<'MARKDOWN'
## Laravel Example

Here is a simple Laravel pattern you can adapt while learning this topic:

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');
```

```php
class DashboardController
{
    public function index(): View
    {
        return view('dashboard.index', [
            'projects' => Project::query()
                ->latest()
                ->paginate(10),
        ]);
    }
}
```

```blade
@foreach ($projects as $project)
    <article>
        <h2>{{ $project->name }}</h2>
        <p>{{ $project->status }}</p>
    </article>
@endforeach
```

The exact code changes depending on the lesson, but the principle stays the same: keep routes clear, controllers focused, validation explicit, views readable, and database queries intentional.
MARKDOWN;
    }

    private function financeTable(): string
    {
        return <<<MARKDOWN
## Simple Finance Table

| Area | Beginner Question | Practical Action |
| --- | --- | --- |
| Income | What money arrived this month? | Track each payment date and source. |
| Expenses | Where did money go? | Group expenses into essentials, business, savings, and flexible spending. |
| Buffer | What happens if income is late? | Build a small emergency fund one step at a time. |
| Review | What should change next month? | Pick one habit to improve instead of changing everything. |

Use the table as an exercise, not as a universal rule. Your personal situation, family responsibilities, business costs, and local obligations matter.
MARKDOWN;
    }

    private function planningTable(array $topic): string
    {
        return <<<MARKDOWN
## Planning Table

| Step | Question | Output |
| --- | --- | --- |
| Problem | What is broken or slow? | A clear one-sentence problem. |
| User | Who needs this most? | A specific reader, client, or team. |
| Workflow | What happens today? | A short process map. |
| First version | What can improve this week? | A small useful action. |
| Review | How will we judge it? | A practical checklist or feedback note. |

This table keeps {$topic['title']} grounded. It turns a broad idea into something you can discuss, build, publish, or review.
MARKDOWN;
    }

    private function relatedLinks(array $topic): string
    {
        $anchors = match ($topic['cluster']) {
            'Laravel' => ['what-is-laravel-and-why-beginners-should-learn-it', 'laravel-routes-explained-from-zero', 'laravel-deployment-checklist'],
            'Finance' => ['personal-finance-for-absolute-beginners', 'budgeting-from-zero', 'freelance-income-management'],
            'SaaS' => ['saas-ideas-in-morocco-for-practical-founders', 'building-a-saas-mvp-without-overbuilding', 'validating-a-saas-idea-before-writing-code'],
            'AI Tools' => ['ai-tools-for-small-businesses-without-hype', 'chatgpt-workflows-for-daily-business-tasks', 'ai-safety-and-privacy-for-small-teams'],
            'Morocco Business' => ['websites-for-moroccan-smes-that-need-more-leads', 'crm-for-moroccan-businesses-explained-simply', 'whatsapp-business-workflows-for-smes'],
            default => ['seo-basics-for-beginners-building-a-blog', 'project-management-for-freelancers', 'documentation-habits-for-small-teams'],
        };

        return collect($anchors)
            ->reject(fn (string $slug): bool => $slug === $topic['base_slug'])
            ->take(5)
            ->map(fn (string $slug): string => '- /posts/'.$slug)
            ->implode("\n");
    }

    private function faqs(array $topic): array
    {
        return [
            [
                'question' => "Is {$topic['title']} suitable for beginners?",
                'answer' => 'Yes. The article is structured to explain the idea from first principles before moving into practical steps and examples.',
            ],
            [
                'question' => 'Should this be published immediately?',
                'answer' => 'No. It is scheduled for admin review first so the editorial team can add screenshots, edits, local examples, and final links.',
            ],
            [
                'question' => 'Does this article make guarantees?',
                'answer' => 'No. It avoids fake claims, fake statistics, guaranteed outcomes, and get-rich-quick promises.',
            ],
        ];
    }

    private function validateSchedule(array $topics): void
    {
        if (count($topics) !== 300) {
            throw new \RuntimeException('The content schedule must contain exactly 300 topics. Current count: '.count($topics));
        }
    }

    private function validateContent(array $topic, string $content): void
    {
        $wordCount = Str::wordCount(strip_tags($content));
        $h2Count = substr_count($content, '## ');

        $blocked = ['as an ai', 'lorem ipsum', 'seeded as draft', 'review this draft', 'guaranteed first million', 'get rich quick'];

        if ($wordCount < 2000) {
            throw new \RuntimeException("{$topic['title']} is too short: {$wordCount} words.");
        }

        if ($h2Count < 5) {
            throw new \RuntimeException("{$topic['title']} needs at least 5 H2 headings.");
        }

        if (! str_contains($content, '## FAQ')) {
            throw new \RuntimeException("{$topic['title']} is missing FAQ section.");
        }

        foreach ($blocked as $phrase) {
            if (str_contains(Str::lower($content), $phrase)) {
                throw new \RuntimeException("{$topic['title']} contains blocked phrase: {$phrase}");
            }
        }

        if (! Str::startsWith($topic['image'], ['https://images.unsplash.com/', 'https://images.pexels.com/', 'https://cdn.pixabay.com/'])) {
            throw new \RuntimeException("{$topic['title']} has an unsupported image URL.");
        }
    }

    private function readingTime(string $content): int
    {
        return max(8, (int) ceil(Str::wordCount(strip_tags($content)) / 220));
    }

    /**
     * @param  array<int, array<string, mixed>>  $topics
     */
    private function writeReports(array $topics, \Illuminate\Support\Collection $categories, \Illuminate\Support\Collection $tags): void
    {
        $start = $topics[0]['scheduled_at'];
        $end = $topics[array_key_last($topics)]['scheduled_at'];
        $byCategory = collect($topics)->countBy('category')->sortKeys();
        $byCluster = collect($topics)->countBy('cluster')->sortKeys();
        $calendar = collect($topics)
            ->groupBy('day')
            ->map(fn ($dayTopics, int $day): string => 'Day '.$day.' - '.$dayTopics->first()['scheduled_at']->toDateString().': '.$dayTopics->pluck('title')->implode(' | '))
            ->implode("\n");

        $report = "# Content Schedule Report\n\n";
        $report .= 'Generated at: '.now(self::TIMEZONE)->toDateTimeString()."\n";
        $report .= "Total posts created/planned: 300\n";
        $report .= "Status: scheduled for admin review, not immediately published\n";
        $report .= 'Schedule start date: '.$start->toDateString()."\n";
        $report .= 'Schedule end date: '.$end->toDateString()."\n";
        $report .= "Publishing rhythm: 5 posts per day for 60 days\n\n";
        $report .= "## Posts By Category\n\n";
        foreach ($byCategory as $category => $count) {
            $report .= "- {$category}: {$count}\n";
        }
        $report .= "\n## Posts By Niche Cluster\n\n";
        foreach ($byCluster as $cluster => $count) {
            $report .= "- {$cluster}: {$count}\n";
        }
        $report .= "\n## Pillar Pages\n\n";
        $report .= "- Laravel: What Is Laravel and Why Beginners Should Learn It\n";
        $report .= "- Finance: Personal Finance for Absolute Beginners\n";
        $report .= "- SaaS: SaaS Ideas in Morocco for Practical Founders\n";
        $report .= "- AI Tools: AI Tools for Small Businesses Without Hype\n";
        $report .= "- Morocco Business: Websites for Moroccan SMEs That Need More Leads\n";
        $report .= "\n## Content Warnings\n\n";
        $report .= "- Finance posts include an educational disclaimer and should be reviewed for local compliance before publishing.\n";
        $report .= "- Articles are long-form generated drafts. Review each draft for screenshots, code accuracy, stronger local examples, and final internal links.\n";
        $report .= "- No fake statistics, fake earnings, fake testimonials, or copied text were intentionally added.\n";
        $report .= "- Image URLs use Unsplash direct image URLs from the allowed royalty-free source pool.\n";
        $report .= "\n## Posts That Need Manual Review\n\n";
        $report .= "All 300 posts need editorial review before publication.\n\n";
        $report .= "## 5 Posts Per Day Calendar\n\n{$calendar}\n";

        $csv = $this->csv($topics);

        Storage::disk('local')->put('content-schedule-report.md', $report);
        Storage::disk('local')->put('content-schedule.csv', $csv);
        file_put_contents(storage_path('app/content-schedule-report.md'), $report);
        file_put_contents(storage_path('app/content-schedule.csv'), $csv);
    }

    /**
     * @param  array<int, array<string, mixed>>  $topics
     */
    private function csv(array $topics): string
    {
        $rows = [[
            'day',
            'scheduled_date',
            'title',
            'slug',
            'category',
            'tags',
            'target_keyword',
            'status',
            'word_count',
            'internal_links',
            'image_url',
        ]];

        foreach ($topics as $topic) {
            $content = $this->content($topic);
            $rows[] = [
                $topic['day'],
                $topic['scheduled_at']->toDateTimeString(),
                $topic['title'],
                $topic['slug'],
                $topic['category'],
                implode('|', $topic['tags']),
                $topic['keyword'],
                'scheduled',
                Str::wordCount(strip_tags($content)),
                str_replace("\n", ' | ', $this->relatedLinks($topic)),
                $topic['image'],
            ];
        }

        return collect($rows)
            ->map(fn (array $row): string => collect($row)
                ->map(fn ($value): string => '"'.str_replace('"', '""', (string) $value).'"')
                ->implode(','))
            ->implode("\n");
    }
}
