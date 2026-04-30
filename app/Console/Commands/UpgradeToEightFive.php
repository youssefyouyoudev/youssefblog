<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UpgradeToEightFive extends Command
{
    protected $signature = 'posts:upgrade-to-8-5
        {--dry-run : Report selected posts and planned changes without saving}
        {--limit=10 : Maximum selected posts to upgrade}
        {--only= : Upgrade one selected post by slug}
        {--draft : Save upgraded posts as draft for review}';

    protected $description = 'Upgrade the highest-value blog posts into stronger authority guides.';

    private const SELECTED_SLUGS = [
        'laravel-seo-checklist-blade-apps',
        'laravel-hosting-morocco',
        'laravel-saas-morocco',
        'laravel-saas-ideas-solo-developer',
        'laravel-deployment-checklist-small-production-apps',
        'freelance-developer-morocco-pricing',
        'business-website-morocco',
        'school-management-saas-morocco',
        'crm-software-morocco',
        'ai-tools-for-small-business',
    ];

    private const FALLBACK_IMAGES = [
        'laravel' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1400&q=80',
        'business' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1400&q=80',
        'ai' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&q=80',
        'freelance' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80',
    ];

    private const BLOCKED_PHRASES = [
        'seeded as draft',
        'admin panel',
        'focus keyword',
        'placeholder',
        'AI-generated',
        'as an AI',
        'generated article',
        'guaranteed rankings',
        'guaranteed traffic',
        'guaranteed income',
        'first million',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $only = $this->option('only') ? Str::slug((string) $this->option('only')) : null;
        $limit = max(1, (int) ($this->option('limit') ?: 10));
        $selectedSlugs = collect(self::SELECTED_SLUGS)
            ->when($only, fn (Collection $slugs) => $slugs->filter(fn (string $slug): bool => $slug === $only))
            ->take($limit)
            ->values();

        if ($only && $selectedSlugs->isEmpty()) {
            $this->error("The slug [{$only}] is not part of the 8.5/10 upgrade set.");

            return self::FAILURE;
        }

        $posts = Post::with(['category', 'tags', 'user'])
            ->whereIn('slug', $selectedSlugs)
            ->get()
            ->sortBy(fn (Post $post): int => $selectedSlugs->search($post->slug))
            ->values();

        if ($posts->count() !== $selectedSlugs->count()) {
            $missing = $selectedSlugs->diff($posts->pluck('slug'))->implode(', ');
            $this->warn("Some selected slugs were not found: {$missing}");
        }

        $allPosts = Post::with(['category', 'tags'])->get();
        $rows = [];

        if (! $dryRun) {
            $this->backupPosts($allPosts);
        }

        foreach ($posts as $post) {
            $oldWords = $this->wordCount($post->content);
            $topic = $this->topic($post);
            $links = $this->relatedLinks($post, $allPosts);
            $content = $this->article($topic, $links);
            $this->validateArticle($post, $topic, $content, $links);
            $payload = $this->payload($post, $topic, $content);
            $changes = $this->changedFields($post, $payload);

            if (! $dryRun) {
                $post->fill($payload)->save();
                $this->syncTags($post, $topic['tags']);
            }

            $rows[] = [
                'slug' => $post->slug,
                'old_title' => $post->title,
                'new_title' => $topic['title'],
                'old_words' => $oldWords,
                'new_words' => $this->wordCount($content),
                'problems' => $this->problems($post, $oldWords),
                'changes' => $changes,
            ];
        }

        $this->writeReport($rows, $dryRun);

        $this->info(($dryRun ? 'Dry run complete' : 'Upgrade complete').": {$posts->count()} selected post(s) processed.");
        $this->line('Report: storage/app/upgrade-to-8-5-report.md');

        return self::SUCCESS;
    }

    private function topic(Post $post): array
    {
        return match ($post->slug) {
            'laravel-seo-checklist-blade-apps',
            'laravel-seo-checklist' => [
                'title' => 'Laravel SEO Checklist for Blade-First Blogs',
                'type' => 'laravel-seo',
                'keyword' => 'Laravel SEO checklist',
                'excerpt' => 'A practical Laravel SEO checklist for Blade blogs covering meta tags, canonicals, sitemap.xml, robots.txt, schema, images, and internal links.',
                'meta' => 'Laravel SEO checklist for Blade blogs: meta tags, canonicals, sitemap.xml, robots.txt, schema, images, and internal links.',
                'tags' => ['Laravel', 'SEO', 'Blade', 'Technical SEO', 'Blogging'],
            ],
            'laravel-hosting-morocco' => [
                'title' => 'Laravel Hosting in Morocco: VPS vs Shared Hosting',
                'type' => 'laravel-hosting',
                'keyword' => 'Laravel hosting Morocco',
                'excerpt' => 'A beginner-friendly hosting guide for Laravel projects in Morocco, comparing shared hosting, VPS, SSL, deployment, permissions, and common server errors.',
                'meta' => 'Compare Laravel hosting in Morocco: shared hosting vs VPS, Nginx, PHP-FPM, SSL, Git deployment, permissions, and common fixes.',
                'tags' => ['Laravel', 'Hosting', 'Morocco', 'VPS', 'Deployment'],
            ],
            'laravel-saas-morocco' => [
                'title' => 'How to Build a Laravel SaaS in Morocco',
                'type' => 'laravel-saas',
                'keyword' => 'Laravel SaaS Morocco',
                'excerpt' => 'A practical guide to building a Laravel SaaS in Morocco, from niche validation and MVP scope to database design, roles, billing, deployment, and support.',
                'meta' => 'Build a Laravel SaaS in Morocco with practical MVP planning, roles, database design, billing choices, deployment, and support workflows.',
                'tags' => ['Laravel', 'SaaS', 'Morocco', 'MVP', 'Business Systems'],
            ],
            'laravel-saas-ideas-solo-developer' => [
                'title' => 'Laravel SaaS Ideas You Can Build as a Solo Developer',
                'type' => 'laravel-saas-ideas',
                'keyword' => 'Laravel SaaS ideas',
                'excerpt' => 'Realistic Laravel SaaS ideas for solo developers, with validation steps, MVP features, database modules, pricing thinking, and launch mistakes to avoid.',
                'meta' => 'Explore realistic Laravel SaaS ideas for solo developers with MVP features, validation steps, pricing thinking, and launch mistakes.',
                'tags' => ['Laravel', 'SaaS', 'Micro SaaS', 'Solo Developer', 'Product Ideas'],
            ],
            'laravel-deployment-checklist-small-production-apps' => [
                'title' => 'Laravel Deployment Checklist for Small Production Apps',
                'type' => 'laravel-deploy',
                'keyword' => 'Laravel deployment checklist',
                'excerpt' => 'A production-minded Laravel deployment checklist covering VPS setup, Nginx, PHP-FPM, SSL, Git pulls, queues, cache commands, storage, and common errors.',
                'meta' => 'Laravel deployment checklist for small production apps: VPS, Nginx, PHP-FPM, SSL, Git pulls, queues, storage, cache, and fixes.',
                'tags' => ['Laravel', 'Deployment', 'Nginx', 'VPS', 'Production'],
            ],
            'freelance-developer-morocco-pricing' => [
                'title' => 'Freelance Developer Pricing in Morocco: Website Cost Guide',
                'type' => 'pricing-morocco',
                'keyword' => 'freelance developer Morocco pricing',
                'excerpt' => 'A clear pricing guide for freelance developers and Moroccan SMEs, covering website scope, MAD package ranges, discovery, payment milestones, and expectations.',
                'meta' => 'Freelance developer pricing in Morocco explained with website scope, MAD package ranges, discovery, payment milestones, and client expectations.',
                'tags' => ['Freelancing', 'Morocco', 'Pricing', 'Web Development', 'Business'],
            ],
            'business-website-morocco' => [
                'title' => 'How to Build a Business Website That Converts Clients',
                'type' => 'business-website',
                'keyword' => 'business website Morocco',
                'excerpt' => 'A practical conversion guide for Moroccan business websites covering offers, trust signals, WhatsApp contact, bilingual pages, local SEO, and lead flow.',
                'meta' => 'Build a business website that converts clients with clear offers, trust signals, WhatsApp flow, bilingual copy, local SEO, and clean CTAs.',
                'tags' => ['Web Development', 'Morocco', 'Local SEO', 'Conversion', 'Business'],
            ],
            'school-management-saas-morocco' => [
                'title' => 'Best Features Every Moroccan School Management SaaS Needs',
                'type' => 'school-saas',
                'keyword' => 'school management SaaS Morocco',
                'excerpt' => 'A practical product guide for Moroccan school management SaaS, covering roles, students, payments, attendance, parent communication, reports, and rollout.',
                'meta' => 'Plan a Moroccan school management SaaS with roles, student records, payments, attendance, parent communication, reports, and rollout.',
                'tags' => ['SaaS', 'Morocco', 'School Management', 'Laravel', 'Business Systems'],
            ],
            'crm-software-morocco' => [
                'title' => 'Why Moroccan SMEs Need CRM Software Before They Scale',
                'type' => 'crm-morocco',
                'keyword' => 'CRM software Morocco',
                'excerpt' => 'A practical CRM guide for Moroccan SMEs covering lead capture, WhatsApp follow-up, pipeline stages, reminders, invoices, reporting, and adoption mistakes.',
                'meta' => 'CRM software in Morocco explained for SMEs: lead capture, WhatsApp follow-up, pipeline stages, reminders, invoices, reports, and adoption.',
                'tags' => ['CRM', 'Morocco', 'SMEs', 'Business Systems', 'SaaS'],
            ],
            'ai-tools-for-small-business' => [
                'title' => 'How AI Tools Help Moroccan Small Businesses Save Time',
                'type' => 'ai-morocco',
                'keyword' => 'AI tools small business Morocco',
                'excerpt' => 'An ethical, practical AI workflow guide for Moroccan SMEs, covering prompts, content review, customer support, privacy, automation limits, and safe adoption.',
                'meta' => 'AI tools for Moroccan small businesses: practical prompts, content review, customer support, privacy warnings, automation limits, and workflows.',
                'tags' => ['AI Tools', 'Morocco', 'Automation', 'Small Business', 'Productivity'],
            ],
            default => [
                'title' => $post->title,
                'type' => 'business-website',
                'keyword' => Str::lower($post->title),
                'excerpt' => $post->excerpt,
                'meta' => $post->meta_description,
                'tags' => $post->tags->pluck('name')->all(),
            ],
        };
    }

    private function article(array $topic, Collection $links): string
    {
        $sections = match ($topic['type']) {
            'laravel-seo' => $this->laravelSeoSections(),
            'laravel-hosting' => $this->laravelHostingSections(),
            'laravel-saas' => $this->laravelSaasSections(),
            'laravel-saas-ideas' => $this->laravelSaasIdeasSections(),
            'laravel-deploy' => $this->laravelDeploySections(),
            'pricing-morocco' => $this->pricingSections(),
            'business-website' => $this->businessWebsiteSections(),
            'school-saas' => $this->schoolSaasSections(),
            'crm-morocco' => $this->crmSections(),
            'ai-morocco' => $this->aiSections(),
            default => $this->businessWebsiteSections(),
        };

        $content = <<<MARKDOWN
{$sections}

## Related Guides Worth Reading

{$this->linksMarkdown($links)}

## Final Checklist

- The topic is explained in plain language.
- The first useful version is small enough to build or review.
- The workflow includes real people, not only tools.
- Risks, limits, and maintenance are visible.
- Internal links point to relevant existing posts.
- The CTA appears once and fits the article naturally.

## FAQ

{$this->faq($topic)}

## Final Summary

{$topic['title']} is strongest when it becomes a practical workflow, not just a nice idea. Start with the real user, write the steps, add only the tools that support those steps, and review the result before expanding.

Need help building a Laravel, SaaS, or business website? [Work with Youssef Youyou](https://youssefyouyou.com).
MARKDOWN;

        while ($this->wordCount($content) < 2500) {
            $content .= "\n\n".$this->deepeningNote($topic);
        }

        return $content;
    }

    private function laravelSeoSections(): string
    {
        return <<<'MARKDOWN'
Laravel SEO is not one plugin or one meta tag. For a Blade-first blog, SEO is the combination of clean URLs, useful titles, crawlable pages, fast templates, internal links, structured data, and content that answers a real question. The good news is that Laravel gives you enough control to build this properly without making the code messy.

This guide is for developers who want their Laravel blog to be understandable by readers and search engines. It avoids magic and focuses on simple pieces you can inspect in your own project.

## What Laravel SEO Really Means

SEO in Laravel starts before the page reaches the browser. A route chooses the page, a controller loads the model, Blade renders the layout, and your SEO component prints the title, description, canonical URL, Open Graph tags, and schema. If one step is sloppy, the final source becomes confusing.

For a blog, each article should have one visible H1 from the article template, a clean title tag, a meta description written from the article itself, a canonical URL using HTTPS, useful internal links, and a sitemap entry only when the article is public.

## Clean Title and Meta Strategy

Use predictable fallbacks. A post title can become `{Post Title} | Youssef Blog`, but long titles should not be forced past a sensible length. Meta descriptions should come from `meta_description`, then `excerpt`, then cleaned article text. Never generate descriptions from rendered layout HTML.

```php
$title = filled($post->meta_title)
    ? $post->meta_title
    : Str::limit($post->title.' | Youssef Blog', 60, '');

$description = filled($post->meta_description)
    ? $post->meta_description
    : Str::limit(strip_tags($post->excerpt), 155, '');
```

## Blade SEO Component

Keep SEO markup in one component so every public page follows the same rules.

```blade
<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
<meta name="twitter:card" content="summary_large_image">
```

## Canonical URLs

Canonical URLs should be self-referencing for normal article pages. They should use HTTPS and the public domain. Avoid `http://`, localhost, duplicate query strings, and category canonicals pointing to unrelated pages.

```php
'canonical' => route('posts.show', $post),
```

If `APP_URL` is correct, Laravel route helpers produce the right domain in production. Use `APP_URL=https://blog.youssefyouyou.com`.

## Sitemap and Robots

A sitemap should include public posts, useful categories, the homepage, and important static pages. It should exclude drafts, private pages, search results, auth routes, and noindex tag pages.

```php
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicController::class, 'robots'])->name('robots');
```

Robots should point to the HTTPS sitemap:

```txt
User-agent: *
Disallow: /admin
Disallow: /login
Sitemap: https://blog.youssefyouyou.com/sitemap.xml
```

## BlogPosting Schema

Schema should match visible content. Do not add fake ratings, fake reviews, or fake offers. A basic `BlogPosting` schema can include headline, description, image, date published, date modified, author, and URL.

## Image SEO and Lazy Loading

Featured images should have alt text and dimensions. Images below the fold should lazy-load. Avoid random image hotlinks and heavy scripts that slow the article.

## Internal Linking for Laravel Blogs

Internal links should help the reader continue the same learning path. Link a Laravel SEO post to hosting, deployment, sitemap, and content strategy posts. Use short anchor text like “Laravel deployment checklist,” not a full paragraph.

## Common Laravel SEO Mistakes

- Reusing one generic meta description everywhere.
- Letting draft or tag pages appear in the sitemap.
- Forgetting canonical URLs on pagination.
- Rendering multiple H1s inside article content.
- Using HTTP URLs in Open Graph tags.
- Publishing thin category pages as important hubs.
MARKDOWN;
    }

    private function laravelHostingSections(): string
    {
        return <<<'MARKDOWN'
Laravel hosting in Morocco usually comes down to a practical decision: do you need the simplicity of shared hosting or the control of a VPS? The right answer depends on the project, the client, the budget, and how much responsibility you can safely handle.

## Shared Hosting vs VPS

Shared hosting can be fine for a small portfolio, a simple business website, or an early blog with limited traffic. It is cheaper and easier, but it often limits queues, background jobs, deployment control, PHP extensions, and server tuning.

A VPS gives you control over Nginx, PHP-FPM, queues, SSL, logs, cron, workers, and deployment. That control is useful for serious Laravel apps, dashboards, SaaS products, and client systems. The trade-off is responsibility: updates, security, backups, and monitoring become your job.

## When Shared Hosting Is Enough

Use shared hosting when the project is mostly static pages, a small blog, or a business website with a contact form. Make sure the host supports the required PHP version, Composer, SSH or deployment access, writable storage, and HTTPS.

For a Moroccan SME, shared hosting may be a good first step if the owner mainly needs a professional presence, WhatsApp contact, and local SEO pages. Do not oversell a VPS if the client cannot maintain it and the project does not need it.

## When a VPS Is Better

Choose a VPS when the app needs queues, scheduled commands, custom Nginx rules, background processing, dashboards, file uploads, webhooks, or real deployment control. A Laravel SaaS, CRM, school management platform, or invoice system usually deserves a VPS.

## Recommended Laravel VPS Stack

- Ubuntu LTS
- Nginx
- PHP-FPM with required extensions
- MySQL or MariaDB
- Redis if queues/cache need it
- Supervisor for queue workers
- Certbot or managed SSL
- Git-based deployment
- Automated backups

## Nginx Server Block

```nginx
server {
    listen 80;
    server_name example.com www.example.com;
    root /var/www/app/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Git Deployment Flow

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Do not paste secrets into commands or commit `.env`. Keep production environment values private.

## Common 500, 403, and 502 Fixes

A 500 often means an application error or wrong `.env` value. Check Laravel logs. A 403 often points to permissions or Nginx root path problems. A 502 usually means PHP-FPM is down, mismatched, or not reachable by Nginx.

## Laravel Hosting Checklist

- HTTPS works without redirect chains.
- `storage` and `bootstrap/cache` are writable.
- Queue workers run if the app uses jobs.
- Scheduler is configured if commands must run.
- Backups exist and are tested.
- Logs are readable.
- `.env` is not public.
MARKDOWN;
    }

    private function laravelSaasSections(): string
    {
        return <<<'MARKDOWN'
Building a Laravel SaaS in Morocco is not only a technical project. It is a business system. The software must solve a repeated problem, fit local buying habits, support realistic workflows, and be maintainable by a small team.

## Start With a Narrow Moroccan Use Case

Good SaaS ideas usually begin with a specific workflow: student registration for private schools, appointment booking for clinics, inventory for shops, invoices for service businesses, or lead follow-up for agencies. The narrower the workflow, the easier it is to build a useful first version.

## Validate Before Writing Too Much Code

Talk to potential users. Ask what they track today, what is painful, what they already pay for, and what would make switching worth it. Do not present the idea as guaranteed. Present it as a workflow you want to understand.

## MVP Feature Scope

- Authentication
- Organization or team model
- Roles and permissions
- One core record type
- Search and filters
- Simple dashboard
- Export or reports
- Email or WhatsApp-ready notifications
- Billing plan notes or manual invoices at first

## Laravel Data Model Example

```php
Schema::create('organizations', function (Blueprint $table): void {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::create('memberships', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('role')->default('member');
    $table->timestamps();
});
```

## Controller Boundary

```php
public function index(Request $request): View
{
    $projects = Project::query()
        ->where('organization_id', $request->user()->current_organization_id)
        ->latest()
        ->paginate(15);

    return view('projects.index', compact('projects'));
}
```

## Moroccan Business Considerations

Many SMEs still prefer WhatsApp, bank transfer, cash, or manual invoice workflows. Your SaaS can still be professional while supporting the way clients actually work. Bilingual French/Arabic labels may matter more than another dashboard chart.

## Launch Strategy

Start with one niche and a small number of users. Offer careful onboarding, listen to support questions, and improve the workflow. A SaaS grows from trust and retention, not only features.
MARKDOWN;
    }

    private function laravelSaasIdeasSections(): string
    {
        return <<<'MARKDOWN'
A solo developer should choose SaaS ideas that are narrow, boring enough to be useful, and small enough to maintain. The goal is not to build a giant platform. The goal is to solve one repeated workflow better than spreadsheets and scattered messages.

## Good SaaS Idea Criteria

The idea should have a clear user, repeated pain, simple records, and a reason to return every week. If the user only needs the tool once, it may be a service project rather than SaaS.

## Practical Ideas

- Appointment CRM for clinics and service businesses.
- Student and payment tracker for private schools.
- Inventory alerts for small shops.
- Quote and invoice tracker for freelancers.
- Maintenance request portal for property managers.
- WhatsApp follow-up dashboard for local service teams.
- Content calendar and SEO tracker for niche blogs.

## MVP Database Shape

Most small SaaS products begin with users, organizations, roles, records, comments, attachments, and activity logs. Laravel handles this well with Eloquent relationships, policies, queues, notifications, and scheduled commands.

```php
class Customer extends Model
{
    protected $fillable = ['organization_id', 'name', 'phone', 'status'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
```

## Validation Questions

Ask users how they do the task today, what breaks, what they have tried, and what would make them pay. Avoid leading questions like “would you use my app?” Ask about real behavior.

## Pricing Thinking

For a solo developer, manual billing can be acceptable at the start. You can validate the service and support model before adding subscriptions. When you do add subscription billing, keep plans simple and tie them to real usage or business size.
MARKDOWN;
    }

    private function laravelDeploySections(): string
    {
        return <<<'MARKDOWN'
Laravel deployment is the moment where local code becomes a real responsibility. A small production app needs repeatable deployment, correct permissions, HTTPS, cache commands, queue handling, backups, and a way to inspect errors.

## Production Deployment Flow

Use a predictable flow. Pull the code, install dependencies, run migrations carefully, build assets, cache config, restart queues, and verify the app. Do not deploy by dragging random files into production.

## Nginx and Public Root

The web root must point to Laravel's `public` directory, not the project root. This protects `.env`, storage files, and application code.

```nginx
root /var/www/example/current/public;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Safe Deployment Commands

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Run commands from the application directory. Do not expose environment secrets in shell history or public logs.

## Queue Worker

If your app sends email, imports files, or processes background jobs, configure a worker with Supervisor or your hosting panel. Restart workers after deployment so they use the new code.

## Storage Permissions

Laravel needs write access to `storage` and `bootstrap/cache`. Incorrect permissions often cause 500 errors after deployment.

## Common Deployment Errors

- 500 after deploy: check Laravel log and `.env`.
- 403: check Nginx root and file permissions.
- 404 for routes: check `try_files` and route cache.
- Assets missing: rebuild Vite and confirm manifest exists.
- Queue stuck: restart worker and inspect failed jobs.
MARKDOWN;
    }

    private function pricingSections(): string
    {
        return <<<'MARKDOWN'
This is general educational information, not legal, tax, or financial advice.

Freelance developer pricing in Morocco is difficult because clients often compare very different things: a simple landing page, a professional business website, a custom Laravel dashboard, and a SaaS platform. A fair price starts with scope, responsibility, and business value, not only pages.

## What Affects Website Cost

Important factors include number of pages, custom design, copywriting, bilingual French/Arabic content, contact forms, WhatsApp workflow, SEO setup, speed, hosting, maintenance, integrations, and whether the project includes custom Laravel logic.

## Practical MAD Package Ranges

These are broad educational examples, not fixed market rules:

| Project type | Typical scope | Example range |
| --- | --- | --- |
| Landing page | One focused page, contact CTA | 1,500-4,000 MAD |
| Business website | 5-8 pages, contact, SEO basics | 4,000-12,000 MAD |
| Laravel dashboard | Auth, CRUD, roles, reports | 10,000-35,000+ MAD |
| SaaS MVP | Multi-user workflow, deployment | 25,000 MAD and up |

The exact number depends on discovery. A cheap project with unclear scope can become expensive in stress.

## Discovery Questions

Ask what the client sells, who visits the site, what action matters, what content exists, which languages are needed, who maintains it, and what happens after launch.

## Payment Milestones

A calm structure might be deposit, first preview, content integration, final review, and launch. Keep expectations written. For Moroccan SMEs, WhatsApp is useful for communication, but scope decisions should still be summarized clearly.

## Common Pricing Mistakes

- Pricing only by page count.
- Forgetting content, revisions, hosting, and maintenance.
- Saying yes before understanding integrations.
- Not explaining what is included.
- Competing only on low price.
MARKDOWN;
    }

    private function businessWebsiteSections(): string
    {
        return <<<'MARKDOWN'
A business website converts clients when it answers the visitor's real questions quickly: what do you offer, who is it for, why should I trust you, what happens next, and how do I contact you?

## Start With The Offer

Many websites fail because the offer is vague. A visitor should understand the service in a few seconds. For a Moroccan business, that may mean clear French and Arabic wording, visible service area, WhatsApp contact, pricing signals, and examples of work.

## Trust Signals That Matter

Useful trust signals include real photos, clear company information, service process, portfolio examples, FAQs, location, phone number, response expectations, and transparent maintenance or support details. Avoid fake testimonials or exaggerated claims.

## Page Structure

- Hero section with clear service and CTA.
- Problem and solution section.
- Services or packages.
- Proof or portfolio.
- Process.
- FAQ.
- Contact form and WhatsApp action.

## WhatsApp Contact Workflow

WhatsApp is often the fastest path for Moroccan SMEs. Use a button with a prepared message, but keep it professional. The message can mention the service and ask the client to describe their need.

## Local SEO Basics

Use clear page titles, local service pages where relevant, structured contact information, image alt text, fast loading, and internal links. Do not stuff city names everywhere. Write naturally for people first.
MARKDOWN;
    }

    private function schoolSaasSections(): string
    {
        return <<<'MARKDOWN'
A Moroccan school management SaaS should reduce daily confusion for administrators, teachers, parents, and students. The best feature list is not the longest one. It is the one that matches the school's real workflow.

## Core Roles

Start with roles: owner, administrator, teacher, parent, and student. Each role should see only what they need. Permissions are not decoration; they protect data and reduce mistakes.

## Student Records

Student profiles should include contact details, class, guardian information, enrollment status, documents, notes, and history. Keep sensitive data controlled and auditable.

## Attendance and Payments

Attendance should be simple enough for teachers to use quickly. Payments should show expected amount, paid amount, remaining balance, due date, and receipt notes. In Morocco, some payments may still be manual, so the system should support clear recording and reconciliation.

## Parent Communication

Parents often prefer WhatsApp or SMS-style updates. The SaaS can prepare messages, reminders, and summaries without pretending every parent will install a new app immediately.

## Reports

Useful reports include unpaid balances, attendance patterns, class lists, teacher workload, and registration status. Reports should help decisions, not overwhelm staff.

## Laravel Module Ideas

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('students', StudentController::class);
    Route::resource('payments', PaymentController::class)->only(['index', 'store']);
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
});
```
MARKDOWN;
    }

    private function crmSections(): string
    {
        return <<<'MARKDOWN'
Moroccan SMEs often lose sales because leads live in scattered WhatsApp chats, notebooks, spreadsheets, and memory. CRM software is useful before scaling because it creates one place for leads, follow-ups, deals, notes, and next actions.

## What A CRM Solves

A CRM helps answer basic questions: who contacted us, what do they need, who should follow up, what was promised, what is the next step, and which opportunities are stuck?

## Simple Pipeline Stages

- New enquiry
- Qualified lead
- Proposal sent
- Waiting for decision
- Won
- Lost
- Follow-up later

These stages are enough for many small teams. Complexity can come later.

## WhatsApp Workflow

WhatsApp can remain the conversation channel, while the CRM stores the lead record and next action. A staff member can copy a prepared message, log the result, and schedule a reminder.

## Invoices and Payments

The CRM does not need full accounting at first. It can track quote status, invoice reference, payment status, and follow-up reminders. Keep legal and tax handling general unless a qualified professional reviews the process.

## Adoption Mistakes

- Making the CRM too complex.
- Not training the team.
- Tracking fields nobody uses.
- Forgetting mobile use.
- Not defining ownership for follow-ups.
MARKDOWN;
    }

    private function aiSections(): string
    {
        return <<<'MARKDOWN'
AI tools can save time for Moroccan small businesses when they support real workflows: writing clearer messages, summarizing notes, preparing FAQs, checking content, and organizing repeated tasks. They become harmful when used for spam, fake claims, or unreviewed automation.

## Ethical AI Starting Point

Use AI as an assistant, not a replacement for responsibility. Do not paste private client data into tools without understanding privacy settings. Do not generate fake reviews, fake screenshots, fake traffic, or fake authority.

## Practical Workflows

- Turn rough WhatsApp notes into a clean follow-up message.
- Draft a bilingual FAQ from real customer questions.
- Summarize meeting notes into next actions.
- Review a service page for unclear wording.
- Create a checklist for onboarding a new client.

## Example Prompt

```text
Review this service page for a Moroccan SME. Point out unclear wording, missing trust signals, and questions a customer might ask before contacting us. Do not invent testimonials, numbers, or guarantees.
```

## Privacy Warnings

Avoid sharing passwords, personal financial details, private client documents, medical data, or confidential contracts. If the workflow is sensitive, anonymize the input or use a controlled internal tool.

## Limitations

AI can sound confident while being wrong. It may miss local context, legal requirements, cultural nuance, or current pricing. Every output needs human review before publication or client use.

## Small Team Adoption Plan

Pick one workflow, write a safe prompt, test it on non-sensitive examples, review output quality, then document when the tool should and should not be used.
MARKDOWN;
    }

    private function faq(array $topic): string
    {
        return <<<MARKDOWN
### Is {$topic['title']} beginner-friendly?

Yes, if you start with the workflow and learn the terms as they appear. Beginners do not need every advanced detail on day one.

### What should I do first?

Write the goal, the target user, and the first useful version. That prevents overbuilding and makes the next step easier.

### How many tools do I need?

Fewer than most people think. Start with the tools required to run the workflow reliably, then add more only when there is a clear reason.

### Does this apply to Morocco?

Yes. Local trust, WhatsApp communication, bilingual French/Arabic context, budget, and realistic adoption habits matter in many Moroccan projects.

### What is the biggest mistake?

The biggest mistake is building or buying a complex solution before the basic workflow is clear.

### Should I publish or launch immediately?

No. Review the work, test the important paths, check wording, and make sure the page or system is useful before pushing it live.
MARKDOWN;
    }

    private function linksMarkdown(Collection $links): string
    {
        if ($links->isEmpty()) {
            return '- No close internal links were added because forcing unrelated links would weaken the article.';
        }

        return $links
            ->map(fn (Post $post): string => '- ['.$post->shortAnchorTitle().'](/posts/'.$post->slug.')')
            ->implode("\n");
    }

    private function deepeningNote(array $topic): string
    {
        return <<<MARKDOWN
## Practical Review Notes for {$topic['keyword']}

When you review this topic in a real project, look for the part where the user becomes uncertain. That uncertainty is usually where better copy, cleaner code, a smaller feature, or a clearer process creates the most value. Strong articles and strong systems both do the same quiet job: they reduce confusion without pretending the work is easier than it is.

For Youssef Youyou Blog, this matters because the audience includes developers, freelancers, and Moroccan SMEs. They need guidance that can survive real budgets, real maintenance, real communication, and real review. Keep the first version useful, document the decision, and improve it from feedback.
MARKDOWN;
    }

    private function payload(Post $post, array $topic, string $content): array
    {
        $imageKey = Str::contains($topic['type'], 'ai') ? 'ai' : (Str::contains($topic['type'], ['pricing', 'business', 'crm', 'school']) ? 'business' : 'laravel');
        $payload = [
            'title' => $topic['title'],
            'excerpt' => $topic['excerpt'],
            'content' => $content,
            'meta_title' => $this->metaTitle($topic['title']),
            'seo_title' => $this->metaTitle($topic['title']),
            'meta_description' => Str::limit($topic['meta'], 158, ''),
            'keywords' => array_values(array_unique([$topic['keyword'], ...$topic['tags']])),
            'faqs' => $this->faqsArray($topic),
            'reading_time' => max(1, (int) ceil($this->wordCount($content) / 220)),
            'last_updated_at' => now(),
            'schema_type' => 'BlogPosting',
        ];

        if ((bool) $this->option('draft')) {
            $payload['status'] = 'draft';
            $payload['published_at'] = null;
        }

        if (! filled($post->featured_image)) {
            $payload['featured_image'] = self::FALLBACK_IMAGES[$imageKey];
            $payload['og_image'] = self::FALLBACK_IMAGES[$imageKey];
            $payload['featured_image_alt'] = $topic['title'];
            $payload['image_credit'] = 'Photo source: Unsplash. Unsplash license allows free use for commercial and non-commercial projects.';
        }

        return array_intersect_key($payload, array_flip(Schema::getColumnListing((new Post)->getTable())));
    }

    private function faqsArray(array $topic): array
    {
        return collect(preg_split('/### /', $this->faq($topic)))
            ->filter()
            ->map(function (string $block): array {
                [$question, $answer] = array_pad(preg_split('/\R\R/', trim($block), 2), 2, '');

                return ['question' => trim($question), 'answer' => trim($answer)];
            })
            ->values()
            ->all();
    }

    private function relatedLinks(Post $post, Collection $allPosts): Collection
    {
        return $allPosts
            ->reject(fn (Post $candidate): bool => $candidate->is($post))
            ->map(function (Post $candidate) use ($post): array {
                $score = $candidate->category_id === $post->category_id ? 4 : 0;
                $score += $candidate->tags->pluck('id')->intersect($post->tags->pluck('id'))->count() * 2;
                $score += collect(preg_split('/[^a-z0-9]+/i', Str::lower($post->title)))
                    ->filter(fn ($word): bool => Str::length($word) > 4)
                    ->intersect(preg_split('/[^a-z0-9]+/i', Str::lower($candidate->title)))
                    ->count();

                return [$candidate, $score];
            })
            ->filter(fn (array $item): bool => $item[1] > 0)
            ->sortByDesc(fn (array $item): int => $item[1])
            ->take(5)
            ->map(fn (array $item): Post => $item[0])
            ->values();
    }

    private function validateArticle(Post $post, array $topic, string $content, Collection $links): void
    {
        if ($this->wordCount($content) < 2000) {
            throw new \RuntimeException("{$post->slug} is under 2000 words.");
        }

        foreach (self::BLOCKED_PHRASES as $phrase) {
            if (Str::contains(Str::lower($content), Str::lower($phrase))) {
                throw new \RuntimeException("{$post->slug} contains blocked phrase [{$phrase}].");
            }
        }

        if (! Str::contains($content, ['## FAQ', '## Final Checklist'])) {
            throw new \RuntimeException("{$post->slug} is missing FAQ or checklist.");
        }

        if (Str::contains($topic['type'], 'laravel') && ! Str::contains($content, '```')) {
            throw new \RuntimeException("{$post->slug} is missing Laravel code examples.");
        }

        if ($topic['type'] === 'ai-morocco' && ! Str::contains(Str::lower($content), ['privacy', 'ethical', 'spam'])) {
            throw new \RuntimeException("{$post->slug} is missing AI safety guidance.");
        }

        preg_match_all('/\/posts\/([a-z0-9-]+)/', $content, $matches);
        $invalid = collect($matches[1] ?? [])->unique()->diff($links->pluck('slug'));

        if ($invalid->isNotEmpty()) {
            throw new \RuntimeException("{$post->slug} has unverified internal links: ".$invalid->implode(', '));
        }
    }

    private function syncTags(Post $post, array $tags): void
    {
        if (! Schema::hasTable('post_tag')) {
            return;
        }

        $ids = collect($tags)->map(fn (string $tag): int => Tag::updateOrCreate(
            ['slug' => Str::slug($tag)],
            ['name' => $tag],
        )->id)->all();

        $post->tags()->syncWithoutDetaching($ids);
    }

    private function backupPosts(Collection $posts): void
    {
        $path = storage_path('app/backups');
        File::ensureDirectoryExists($path);
        $filename = 'posts-before-8-5-upgrade-'.now('Africa/Casablanca')->format('Y-m-d-His').'.json';

        File::put($path.'/'.$filename, $posts->map(fn (Post $post): array => [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'status' => $post->status,
            'published_at' => $post->published_at?->toDateTimeString(),
            'excerpt' => $post->getRawOriginal('excerpt'),
            'content' => $post->content,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'keywords' => $post->keywords,
            'faqs' => $post->faqs,
            'tags' => $post->tags->pluck('slug')->values()->all(),
        ])->values()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function writeReport(array $rows, bool $dryRun): void
    {
        $report = "# Upgrade To 8.5 Report\n\n";
        $report .= 'Generated at: '.now('Africa/Casablanca')->toDateTimeString()."\n";
        $report .= 'Mode: '.($dryRun ? 'dry run' : 'saved changes')."\n";
        $report .= "Backup: storage/app/backups/posts-before-8-5-upgrade-{date}.json on saved runs\n\n";
        $report .= "## Posts Selected\n\n";

        foreach ($rows as $row) {
            $report .= "- {$row['slug']}: {$row['old_words']} -> {$row['new_words']} words\n";
            $report .= "  Old title: {$row['old_title']}\n";
            $report .= "  New title: {$row['new_title']}\n";
            $report .= "  Detected problems: ".($row['problems'] ? implode(', ', $row['problems']) : 'none')."\n";
            $report .= "  Planned/applied changes: ".($row['changes'] ? implode(', ', $row['changes']) : 'content review only')."\n";
        }

        $report .= "\n## Manual Review Notes\n\n";
        $report .= "- Review code snippets against the production server before publishing Laravel guides.\n";
        $report .= "- Morocco business pricing examples are educational ranges, not legal, tax, or financial advice.\n";
        $report .= "- AI guidance intentionally avoids spam, fake traffic, and unreviewed automation.\n";

        File::put(storage_path('app/upgrade-to-8-5-report.md'), $report);
    }

    private function problems(Post $post, int $oldWords): array
    {
        $problems = [];
        $content = $post->content ?? '';

        if ($oldWords < 2000) {
            $problems[] = 'thin content';
        }

        if (substr_count($content, '```') === 0 && Str::contains(Str::lower($post->title), 'laravel')) {
            $problems[] = 'Laravel post lacks code examples';
        }

        foreach (['Why This Matters Now', 'The Simple Starting Point', 'A Practical Workflow', 'Start With One Measurable Outcome'] as $phrase) {
            if (Str::contains($content, $phrase)) {
                $problems[] = "generic section: {$phrase}";
            }
        }

        foreach (self::BLOCKED_PHRASES as $phrase) {
            if (Str::contains(Str::lower($content), Str::lower($phrase))) {
                $problems[] = "blocked phrase: {$phrase}";
            }
        }

        return array_values(array_unique($problems));
    }

    private function changedFields(Post $post, array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $key): bool => $post->getAttribute($key) != $value)
            ->keys()
            ->values()
            ->all();
    }

    private function metaTitle(string $title): string
    {
        return Str::length($title.' | Youssef Blog') <= 60 ? $title.' | Youssef Blog' : Str::limit($title, 60, '');
    }

    private function wordCount(?string $content): int
    {
        return Str::wordCount(strip_tags($content ?? ''));
    }
}
