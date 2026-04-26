<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ScheduledNinetyPostsSeeder extends Seeder
{
    private const TIMEZONE = 'Africa/Casablanca';

    private const TIMES = ['09:00', '13:00', '18:00'];

    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@youssefyouyou.com'],
            ['name' => 'Youssef Youyou', 'password' => Hash::make('password'), 'role' => 'admin'],
        );

        $categories = collect($this->categories())->mapWithKeys(fn (array $category): array => [
            $category['name'] => Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'seo_title' => $category['seo_title'],
                    'meta_description' => $category['meta_description'],
                ],
            ),
        ]);

        $posts = collect($this->posts())->values();
        $tags = $posts
            ->flatMap(fn (array $post): array => $post['tags'])
            ->unique()
            ->mapWithKeys(fn (string $tag): array => [
                $tag => Tag::updateOrCreate(['slug' => Str::slug($tag)], ['name' => $tag]),
            ]);

        $startDate = CarbonImmutable::tomorrow(self::TIMEZONE);

        $posts->each(function (array $data, int $index) use ($admin, $categories, $tags, $startDate): void {
            [$hour, $minute] = explode(':', self::TIMES[$index % 3]);
            $publishAt = $startDate->addDays(intdiv($index, 3))->setTime((int) $hour, (int) $minute);
            $content = $this->content($data);

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
                    'status' => 'scheduled',
                    'published_at' => $publishAt,
                    'seo_title' => $data['seo_title'],
                    'meta_description' => $data['meta_description'],
                    'keywords' => $data['keywords'],
                    'faqs' => $this->faqs($data),
                    'og_image' => $data['image'],
                    'reading_time' => max(5, (int) ceil(Str::wordCount(strip_tags($content)) / 220)),
                    'is_featured' => in_array($data['slug'], $this->featuredSlugs(), true),
                ],
            );

            $post->tags()->sync(collect($data['tags'])->map(fn (string $tag): int => $tags[$tag]->id));
        });
    }

    private function categories(): array
    {
        return [
            ['name' => 'Finance', 'description' => 'Budgeting, saving, freelancer finance, fintech tools, and beginner-friendly investing education.', 'seo_title' => 'Finance Guides for Freelancers and Online Builders', 'meta_description' => 'Practical 2026 finance guides about budgeting, saving, online income, fintech tools, and safer money systems.'],
            ['name' => 'Tech', 'description' => 'Developer laptops, hosting, VPS, cybersecurity, productivity, domains, backups, and remote-work tools.', 'seo_title' => 'Tech Guides for Developers and Digital Builders', 'meta_description' => 'Helpful 2026 tech guides for developers, remote workers, content sites, hosting, cybersecurity, and productivity.'],
            ['name' => 'AI', 'description' => 'AI tools, agents, ChatGPT workflows, prompt engineering, automation, students, freelancers, and content marketing.', 'seo_title' => 'AI Guides for Freelancers, Students, and Small Businesses', 'meta_description' => 'Practical 2026 AI guides covering tools, agents, ChatGPT workflows, automation, content, and productivity systems.'],
            ['name' => 'Laravel', 'description' => 'Laravel SEO, deployment, performance, security, queues, scheduler, backups, admin panels, and SaaS ideas.', 'seo_title' => 'Laravel Guides for SEO, Deployment, and SaaS Builders', 'meta_description' => 'Laravel 12 guides for SEO, Ubuntu deployment, performance, security, queues, scheduler, SaaS ideas, and admin panels.'],
            ['name' => 'Business', 'description' => 'SaaS ideas, freelancing, agency growth, pricing, client acquisition, digital services, portfolios, and local business.', 'seo_title' => 'Business Guides for Freelancers, Agencies, and SaaS Builders', 'meta_description' => 'Practical 2026 business guides for SaaS ideas, freelancing, agencies, client acquisition, content marketing, and digital services.'],
        ];
    }

    private function posts(): array
    {
        $images = $this->images();

        return [
            ...$this->buildPosts('Finance', $this->financeTopics(), $images['finance']),
            ...$this->buildPosts('Tech', $this->techTopics(), $images['tech']),
            ...$this->buildPosts('AI', $this->aiTopics(), $images['ai']),
            ...$this->buildPosts('Laravel', $this->laravelTopics(), $images['laravel']),
            ...$this->buildPosts('Business', $this->businessTopics(), $images['business']),
        ];
    }

    private function financeTopics(): array
    {
        return [
            ['Budgeting for Freelancers in Morocco in 2026', 'budgeting for freelancers Morocco 2026', ['Budgeting', 'Morocco', 'Freelance Finance'], 'A practical monthly budgeting system for Moroccan freelancers with irregular income, platform fees, and online payments.', 'separating income, taxes, savings, tools, and living costs before money becomes confusing', 'A designer receives three client payments in one week and uses buckets for bills, taxes, savings, and reinvestment.'],
            ['How to Save Money With Low Income Without Extreme Cuts', 'saving money with low income 2026', ['Saving Money', 'Budgeting', 'Money Habits'], 'A realistic saving plan for people with low income who need small wins, simple rules, and fewer surprise expenses.', 'finding small leaks and building momentum without shame or unrealistic restrictions', 'A student starts by saving the price of two unused subscriptions instead of trying to save half their income.'],
            ['Online Income Ideas That Fit Beginners in Morocco', 'online income ideas Morocco beginners', ['Online Income', 'Morocco', 'Side Hustles'], 'A grounded list of online income paths for beginners, including services, content, digital products, and local business support.', 'matching skills to realistic offers before chasing passive income claims', 'A beginner offers website fixes to local businesses before launching a larger product.'],
            ['Emergency Fund Guide for Freelancers and Builders', 'emergency fund guide freelancers 2026', ['Emergency Fund', 'Freelance Finance', 'Saving Money'], 'A step-by-step emergency fund system for freelancers who deal with delayed payments and uneven monthly income.', 'building a buffer that protects decisions when invoices arrive late', 'A developer keeps one month of core expenses separate from business tool money.'],
            ['Best Finance App Categories for Freelancers in 2026', 'finance apps for freelancers 2026', ['Finance Tools', 'Fintech', 'Freelance Finance'], 'Learn which finance app categories matter most: invoicing, banking, budgeting, subscriptions, and tax planning.', 'choosing tool categories by financial job instead of collecting apps', 'A freelancer uses one invoicing tool, one bank export, and one weekly cash-flow sheet.'],
            ['Side Hustle Budgeting: How to Reinvest Without Overspending', 'side hustle budgeting 2026', ['Side Hustles', 'Budgeting', 'Online Income'], 'A simple budget for side hustles that balances learning, tools, ads, domains, and savings.', 'controlling reinvestment so the project does not quietly drain personal cash', 'A creator caps software spending until their newsletter earns its first consistent revenue.'],
            ['Avoiding Debt While Building an Online Business', 'avoid debt online business', ['Debt Avoidance', 'Online Business', 'Finance'], 'How to build online projects with low-risk spending rules, staged upgrades, and simple cash-flow checkpoints.', 'using constraints to avoid debt-driven decisions', 'A founder starts with manual delivery before paying for automation tools.'],
            ['Beginner Investing Education: What to Learn Before You Invest', 'beginner investing education 2026', ['Investing Education', 'Risk Management', 'Money Basics'], 'An educational guide to risk, fees, diversification, time horizon, and why this is not personal financial advice.', 'learning investing concepts before taking risk', 'A beginner compares fees and time horizon before choosing any platform or product.'],
            ['Personal Finance System for Developers With Side Projects', 'personal finance system developers', ['Personal Finance', 'Developers', 'Side Projects'], 'A finance system for developers balancing salaries, freelance income, hosting bills, courses, and product experiments.', 'turning project costs into planned decisions instead of random charges', 'A Laravel developer creates a monthly tool budget before buying another SaaS subscription.'],
            ['Digital Payments for Online Business in Morocco', 'digital payments online business Morocco', ['Digital Payments', 'Morocco', 'Online Business'], 'A practical overview of payment considerations for Moroccan online businesses, including fees, records, and trust.', 'tracking payment methods and fees clearly for better pricing decisions', 'A consultant records processor fees separately so pricing reflects real take-home income.'],
            ['Cash-Flow Planning for Freelancers With Irregular Clients', 'cash flow planning freelancers', ['Cash Flow', 'Freelance Finance', 'Client Work'], 'A simple cash-flow planning routine for freelancers who receive uneven payments from several clients.', 'forecasting the next 30 days instead of reacting to the bank balance', 'A copywriter lists expected invoice dates before accepting a discounted project.'],
            ['How to Audit Subscriptions and Save Money in One Afternoon', 'audit subscriptions save money', ['Saving Money', 'Subscriptions', 'Budgeting'], 'A one-afternoon subscription audit that helps online builders cut unused software without hurting productivity.', 'removing silent recurring costs from the business budget', 'A creator cancels overlapping design tools and keeps the one used every week.'],
            ['Budgeting Tools vs Spreadsheets: What Should You Use?', 'budgeting tools vs spreadsheets', ['Budgeting', 'Finance Tools', 'Productivity'], 'A practical comparison of budgeting apps and spreadsheets for freelancers, students, and online business owners.', 'choosing the simplest tool that creates a weekly money habit', 'A freelancer starts with a spreadsheet, then upgrades when bank imports save real time.'],
            ['How to Price Digital Services Without Ignoring Expenses', 'price digital services expenses', ['Digital Services', 'Pricing', 'Freelance Finance'], 'A finance-first pricing guide for digital services that accounts for tools, taxes, revisions, and admin time.', 'pricing based on delivery reality, not only hours worked', 'A web designer adds discovery and revision time before quoting a landing page.'],
            ['Money Rules for Students Starting Online Income in 2026', 'money rules students online income', ['Students', 'Online Income', 'Budgeting'], 'Simple money rules for students earning online for the first time, with safety, savings, and reinvestment basics.', 'building habits before income becomes inconsistent', 'A student saves a small percentage from each project before buying better gear.'],
            ['How to Build a 50-30-20 Budget for Irregular Income', '50 30 20 budget irregular income', ['Budgeting', 'Freelance Finance', 'Saving Money'], 'Adapt the 50-30-20 budgeting idea for irregular freelance or online business income.', 'using percentages flexibly when income changes every month', 'A freelancer calculates percentages after setting aside tax and platform fees.'],
            ['Finance Checklist Before Starting a Side Hustle', 'finance checklist before side hustle', ['Side Hustles', 'Budgeting', 'Money Basics'], 'A finance checklist to review before buying domains, tools, templates, courses, or ads for a side project.', 'checking costs and runway before excitement turns into overspending', 'A builder validates demand with manual outreach before paying for a full tool stack.'],
            ['How to Track Profit From Digital Products', 'track profit digital products', ['Digital Products', 'Online Income', 'Finance Tools'], 'A simple profit-tracking system for templates, ebooks, courses, and small digital products.', 'separating revenue, fees, refunds, tools, and support time', 'A template seller tracks refunds and platform fees before judging the product.'],
        ];
    }

    private function techTopics(): array
    {
        return [
            ['Best Laptops for Developers in 2026: What Matters Most', 'best laptops for developers 2026', ['Developer Gear', 'Coding Tools', 'Productivity'], 'A practical developer laptop buying guide focused on RAM, CPU, battery, keyboard, screen, and longevity.', 'buying a laptop based on daily development work instead of marketing specs', 'A Laravel developer chooses more RAM and a better keyboard over a flashy GPU they rarely need.'],
            ['Best VPS Hosting for Laravel Projects in 2026', 'best VPS hosting for Laravel', ['VPS', 'Laravel Hosting', 'Deployment'], 'A VPS decision guide for Laravel blogs, SaaS MVPs, and small production apps.', 'choosing a VPS by reliability, backups, support, and deployment workflow', 'A small SaaS project starts with one properly configured VPS before adding more infrastructure.'],
            ['Shared Hosting vs VPS: What Builders Should Choose', 'shared hosting vs VPS 2026', ['Hosting', 'VPS', 'Performance'], 'A plain-English comparison of shared hosting and VPS hosting for content sites and Laravel apps.', 'matching hosting type to project complexity and maintenance capacity', 'A simple brochure site can use shared hosting, while a queue-based Laravel app needs more control.'],
            ['Cybersecurity Basics for Freelancers in 2026', 'cybersecurity basics freelancers 2026', ['Cybersecurity', 'Freelancing', 'Security'], 'Essential security habits for freelancers handling client files, passwords, email, and payment accounts.', 'reducing preventable account and client-data risks', 'A freelancer turns on 2FA before connecting client domains or analytics accounts.'],
            ['Productivity Tools for Developers Who Work Remotely', 'productivity tools remote developers', ['Remote Work', 'Productivity', 'Developer Tools'], 'A focused remote-work tool stack for notes, tasks, screen recording, calls, and async communication.', 'reducing communication friction without adding more apps than needed', 'A remote developer uses one task board and one decision log instead of scattered chat messages.'],
            ['Developer Setup Checklist for a New Windows Machine', 'developer setup checklist Windows 2026', ['Developer Setup', 'Windows', 'Coding Tools'], 'A practical setup checklist for terminal, editor, Git, databases, Node, PHP, and project backups.', 'making a new machine ready for real client and product work', 'A developer documents install steps so the next machine setup takes one hour instead of a full day.'],
            ['Website Speed Optimization Checklist for Content Sites', 'website speed optimization checklist', ['Performance', 'SEO', 'Websites'], 'A speed checklist for images, CSS, JavaScript, fonts, caching, hosting, and Core Web Vitals.', 'improving speed in ways readers and crawlers can feel', 'A blog compresses hero images and removes unused scripts before changing hosting.'],
            ['Best Tools for Remote Work and Client Collaboration', 'best tools remote work client collaboration', ['Remote Work', 'Client Work', 'Productivity'], 'Useful tool categories for remote collaboration: docs, tasks, calls, files, signatures, and reporting.', 'keeping clients informed without endless meetings', 'An agency sends one weekly status report instead of answering scattered messages all day.'],
            ['Domain and DNS Basics for Online Business Owners', 'domain DNS basics online business', ['DNS', 'Domains', 'Hosting'], 'Understand domains, DNS records, email records, SSL, and common mistakes when launching a brand.', 'building domain confidence before changing production records', 'A founder checks MX records before moving a website to avoid breaking email.'],
            ['Backup Strategy for Websites, Code, and Client Files', 'backup strategy websites code client files', ['Backups', 'Security', 'Developer Tools'], 'A simple backup system covering code, databases, uploads, client files, passwords, and cloud drives.', 'protecting work before a laptop, server, or account fails', 'A developer tests database restore once per quarter instead of assuming backups work.'],
            ['Best Code Editor Extensions for Laravel and Content Work', 'best code editor extensions Laravel content', ['Coding Tools', 'Laravel', 'Productivity'], 'A lightweight extension setup for Laravel, Blade, formatting, search, snippets, and writing content.', 'speeding up daily work without turning the editor into a slow dashboard', 'A developer keeps only extensions used weekly and removes noisy ones.'],
            ['How to Choose Hosting for a New Blog', 'choose hosting for new blog 2026', ['Hosting', 'Blogging', 'Performance'], 'A beginner-friendly hosting guide for blogs that may later add Laravel features or monetization.', 'choosing hosting that fits the next six months', 'A new publisher chooses simple hosting first, then upgrades after traffic and features justify it.'],
            ['Tech Stack for a Solo Online Business in 2026', 'tech stack solo online business 2026', ['Business Tools', 'Software Stack', 'Productivity'], 'A practical tool stack for publishing, email, analytics, payments, automation, and support.', 'keeping the stack small enough to maintain alone', 'A solo founder uses fewer tools and documents weekly workflows clearly.'],
            ['How to Secure Your Domain, Email, and Social Accounts', 'secure domain email social accounts', ['Cybersecurity', 'Domains', 'Security'], 'A security checklist for brand accounts that are expensive to lose.', 'protecting the identity layer of an online business', 'A creator stores recovery codes before launching a newsletter campaign.'],
            ['Cloud Storage Workflow for Freelancers and Agencies', 'cloud storage workflow freelancers agencies', ['Cloud Storage', 'Agency', 'Productivity'], 'Organize client files, contracts, exports, backups, and delivery assets without chaos.', 'creating predictable file workflows for client work', 'An agency uses one folder template for every client project.'],
            ['How to Evaluate Software Before Paying Monthly', 'evaluate software before paying monthly', ['Software Stack', 'Budgeting', 'Productivity'], 'A buying framework for SaaS subscriptions based on use case, switching cost, and saved time.', 'preventing subscription creep while keeping useful tools', 'A freelancer tests a tool on one project before annual billing.'],
            ['Remote Work Hardware Setup for Developers', 'remote work hardware setup developers', ['Developer Gear', 'Remote Work', 'Productivity'], 'A practical hardware setup for posture, calls, coding, internet backup, and focus.', 'improving daily comfort without overspending on gear', 'A developer upgrades lighting and microphone before buying a second monitor.'],
            ['How to Monitor Website Uptime Without Overcomplicating It', 'monitor website uptime simple', ['Monitoring', 'Hosting', 'Websites'], 'Simple uptime monitoring, alerting, and response habits for small websites and Laravel apps.', 'knowing when the site is down before users tell you', 'A publisher sets one uptime monitor and a basic response checklist.'],
        ];
    }

    private function aiTopics(): array
    {
        return [
            ['Best AI Tools 2026: Practical Stack for Builders', 'best AI tools 2026', ['AI Tools', 'Productivity', 'Automation'], 'A practical AI tools stack for research, writing, coding, support, automation, and business planning.', 'choosing AI tools by job-to-be-done instead of hype', 'A freelancer uses one AI tool for research and another for meeting summaries, not ten overlapping apps.'],
            ['AI Agents for Small Businesses: Where to Start', 'AI agents for small business 2026', ['AI Agents', 'Small Business', 'Automation'], 'A beginner-friendly AI agent guide for support, admin work, lead research, reporting, and reminders.', 'starting with low-risk internal workflows before customer-facing automation', 'A shop owner creates an agent to summarize inquiries before automating replies.'],
            ['ChatGPT Workflows for Freelancers and Creators', 'ChatGPT workflows freelancers creators', ['ChatGPT', 'Freelancing', 'Content Workflow'], 'Repeatable ChatGPT workflows for proposals, outlines, briefs, research, editing, and client communication.', 'turning prompts into reusable workflows with human review', 'A writer uses ChatGPT for outline alternatives, then adds original examples from client work.'],
            ['AI Tools for Freelancers: Save Time Without Losing Trust', 'AI tools for freelancers 2026', ['AI Tools', 'Freelancing', 'Client Work'], 'A practical AI stack for freelancers that improves delivery while keeping transparency and quality control.', 'using AI as a support system, not a replacement for judgment', 'A designer uses AI for moodboard research but still creates the final direction manually.'],
            ['AI Tools for Students: Study Smarter in 2026', 'AI tools for students 2026', ['AI Tools', 'Students', 'Productivity'], 'Responsible AI study workflows for summaries, quizzes, flashcards, revision plans, and exam preparation.', 'learning faster without outsourcing understanding', 'A student asks AI for practice questions after writing their own notes.'],
            ['AI Automation Ideas for Small Online Businesses', 'AI automation ideas online business', ['AI Automation', 'Online Business', 'Productivity'], 'Automation ideas for content calendars, support drafts, invoice reminders, research, and reporting.', 'automating repetitive work while keeping important decisions human', 'A newsletter owner automates topic collection but reviews every final title.'],
            ['Prompt Engineering Basics for Useful Business Outputs', 'prompt engineering basics business', ['Prompt Engineering', 'AI Tools', 'Business'], 'A simple prompt framework for context, role, examples, constraints, and review criteria.', 'improving AI output by giving better inputs and checks', 'A consultant includes audience, tone, and success criteria before asking for a proposal draft.'],
            ['AI for Content Marketing: Helpful Content Workflow', 'AI for content marketing workflow', ['AI Content', 'Content Marketing', 'SEO'], 'Use AI for research, outlines, content briefs, repurposing, and quality checks without publishing generic posts.', 'making content more useful rather than just faster', 'A blogger uses AI to find missing questions, then answers them with personal experience.'],
            ['AI Productivity System for Busy Freelancers', 'AI productivity system freelancers', ['AI Productivity', 'Freelancing', 'Automation'], 'Build a weekly AI productivity system for planning, inbox review, client summaries, and focus blocks.', 'making AI part of a weekly operating rhythm', 'A freelancer reviews tasks every Monday and asks AI to group similar admin work.'],
            ['AI Side Hustles: Realistic Ideas and Risks', 'AI side hustles realistic ideas', ['AI Side Hustles', 'Online Income', 'Risk Management'], 'A realistic guide to AI-assisted services, templates, research packs, and automations with risk warnings.', 'choosing AI side hustles that solve real problems', 'A beginner sells workflow setup help instead of claiming passive AI income.'],
            ['AI Tools for Customer Support in Small Businesses', 'AI customer support small business', ['AI Agents', 'Customer Support', 'Small Business'], 'How to use AI for support drafts, tagging, knowledge bases, and escalation without harming trust.', 'making support faster while keeping human responsibility', 'A service business lets AI draft replies but requires approval for refunds or complaints.'],
            ['How to Build a Personal AI Research Workflow', 'personal AI research workflow', ['AI Research', 'Productivity', 'ChatGPT'], 'A practical workflow for collecting sources, summarizing notes, checking claims, and turning research into action.', 'using AI to organize research without replacing verification', 'A creator saves original links before asking AI for a summary.'],
            ['AI Tools for Email, Proposals, and Follow-Up', 'AI tools email proposals follow up', ['AI Tools', 'Client Acquisition', 'Freelancing'], 'Use AI to draft better emails, proposals, follow-ups, and summaries while staying personal.', 'improving communication speed without sounding automated', 'A freelancer writes a custom intro, then uses AI to tighten the structure.'],
            ['AI Automation Checklist Before Connecting Your Apps', 'AI automation checklist apps', ['AI Automation', 'Security', 'Productivity'], 'A checklist for permissions, data privacy, review steps, failure cases, and maintenance before automation.', 'avoiding risky automations that expose data or create bad customer experiences', 'A business tests automation on internal notes before connecting client emails.'],
            ['ChatGPT Prompt Library for Online Business Owners', 'ChatGPT prompt library online business', ['ChatGPT', 'Online Business', 'Prompt Engineering'], 'A practical prompt library for content planning, customer research, FAQs, SOPs, and weekly reviews.', 'creating reusable prompts that reflect your business context', 'A founder keeps prompts in a document and improves them after each use.'],
            ['AI Tools for Moroccan Freelancers Working With Global Clients', 'AI tools Moroccan freelancers global clients', ['AI Tools', 'Morocco', 'Freelancing'], 'AI workflows for translation, proposals, research, meeting notes, and client delivery across markets.', 'helping Moroccan freelancers compete with clearer communication and faster delivery', 'A freelancer uses AI to polish English proposals while keeping the offer specific.'],
            ['How to Evaluate AI Tools Before Paying for Them', 'evaluate AI tools before paying', ['AI Tools', 'Budgeting', 'Productivity'], 'A decision framework for AI subscriptions based on output quality, privacy, integrations, and saved time.', 'testing AI tools with real work before subscribing', 'A business owner compares two tools using the same customer support task.'],
            ['AI for Local Businesses: Simple Wins Before Complex Agents', 'AI for local businesses simple wins', ['AI Agents', 'Local Business', 'Automation'], 'Simple AI use cases for local businesses: FAQs, social posts, review replies, inventory notes, and training docs.', 'starting with simple high-friction tasks', 'A local service business builds a FAQ assistant before attempting automated bookings.'],
        ];
    }

    private function laravelTopics(): array
    {
        return [
            ['Laravel SEO Guide 2026 for Blade Blogs', 'Laravel SEO guide 2026', ['Laravel SEO', 'Blade', 'Technical SEO'], 'A Laravel SEO guide for metadata, canonical URLs, sitemaps, RSS, schema, internal links, and speed.', 'making server-rendered Laravel pages easy for crawlers and useful for readers', 'A Blade blog generates Article schema and a clean sitemap before publishing clusters.'],
            ['Laravel Deployment on Ubuntu: Practical Checklist', 'Laravel deployment Ubuntu 2026', ['Laravel Deployment', 'Ubuntu', 'Nginx'], 'A production checklist for deploying Laravel on Ubuntu with Nginx, PHP-FPM, queues, cache, and SSL.', 'turning deployment into a repeatable checklist', 'A developer deploys with cached config and verified permissions before opening traffic.'],
            ['Laravel Performance Checklist for Growing Blogs', 'Laravel performance checklist 2026', ['Laravel Performance', 'Caching', 'Eloquent'], 'Speed up Laravel blogs with eager loading, pagination, caching, Vite builds, and image discipline.', 'protecting page speed as content grows', 'A blog avoids N+1 queries by eager loading categories and tags on post cards.'],
            ['Laravel Security Basics for Admin Publishing Systems', 'Laravel security admin publishing', ['Laravel Security', 'Admin Panel', 'Validation'], 'Secure admin routes, forms, middleware, CSRF, validation, noindex, and safe Blade output.', 'building a publishing admin that is private and predictable', 'An admin panel uses Form Requests and role middleware before adding more features.'],
            ['Laravel Queues Explained for Beginners', 'Laravel queues beginners 2026', ['Laravel Queues', 'Background Jobs', 'Performance'], 'A beginner-friendly guide to queues for emails, imports, image processing, and slow tasks.', 'moving slow work out of the request cycle', 'A blog queues newsletter emails instead of sending them during form submission.'],
            ['Laravel Scheduler Guide for Content Websites', 'Laravel scheduler content websites', ['Laravel Scheduler', 'Scheduled Posts', 'Automation'], 'Use Laravel scheduler for publishing posts, clearing stale data, backups, reports, and maintenance tasks.', 'automating routine site operations safely', 'A blog runs scheduled publishing every minute and logs how many posts changed status.'],
            ['Laravel SaaS Starter Ideas for Solo Developers', 'Laravel SaaS starter ideas 2026', ['Laravel SaaS', 'SaaS Ideas', 'Solo Developer'], 'Practical Laravel SaaS ideas for bookings, invoices, agencies, education, and local business workflows.', 'choosing small SaaS ideas with clear users and repeat problems', 'A solo developer validates a booking tool with three local businesses before building subscriptions.'],
            ['Laravel Blade vs React for SEO Content Sites', 'Laravel Blade vs React SEO', ['Blade', 'SEO', 'Laravel'], 'A practical comparison of Blade and React for SEO-focused content sites and admin-backed blogs.', 'choosing server-rendered Blade when crawlability and speed matter most', 'A publisher uses Blade for public pages and avoids a SPA for article routes.'],
            ['Laravel Backup System for Small Production Apps', 'Laravel backup system production', ['Laravel Backups', 'Security', 'DevOps'], 'Plan backups for database, uploads, environment, deployment scripts, and restore testing.', 'treating restore tests as part of the backup system', 'A Laravel app schedules database backups and verifies restore on a staging database.'],
            ['Laravel Admin Panel Checklist for Blog Publishing', 'Laravel admin panel blog publishing', ['Admin Panel', 'Laravel', 'Publishing'], 'A checklist for post CRUD, categories, tags, SEO fields, image previews, status filters, and scheduled publishing.', 'building only the admin features editors need to publish consistently', 'An admin table filters scheduled posts so future content is easy to review.'],
            ['Laravel Sitemap XML for Published Posts Only', 'Laravel sitemap published posts only', ['Sitemap', 'Laravel SEO', 'Scheduled Posts'], 'Build a sitemap that includes public pages and published posts while excluding drafts and future scheduled posts.', 'keeping search engines away from future content', 'A scheduled post does not enter sitemap.xml until it becomes publicly visible.'],
            ['Laravel RSS Feed for a Niche Blog', 'Laravel RSS feed niche blog', ['RSS', 'Laravel SEO', 'Content'], 'Create an RSS feed for published articles with titles, excerpts, dates, categories, and canonical URLs.', 'making content easy to follow outside social platforms', 'A reader subscribes to the feed and sees only published posts.'],
            ['Laravel Form Requests for SEO Blog Admins', 'Laravel Form Requests SEO blog admin', ['Validation', 'Admin Panel', 'Laravel'], 'Use Form Requests to validate posts, categories, tags, SEO metadata, images, and publish dates.', 'keeping admin validation consistent as fields grow', 'A PostRequest validates canonical URLs and meta description length before saving.'],
            ['Laravel Soft Deletes for Publishing Safety', 'Laravel soft deletes publishing safety', ['Soft Deletes', 'Publishing', 'Laravel'], 'Use soft deletes to recover accidentally deleted posts while keeping admin cleanup manageable.', 'adding a safety layer to editorial workflows', 'An editor deletes the wrong draft and restores it before launch.'],
            ['Laravel Eloquent Scopes for Public Visibility', 'Laravel Eloquent scopes public visibility', ['Eloquent', 'Scheduled Posts', 'Laravel SEO'], 'Create scopes that keep drafts and future scheduled posts hidden from public pages and sitemap.', 'centralizing visibility rules in the model', 'A controller calls latestPublished and never repeats complex status logic.'],
            ['Laravel Image Fields for SEO and Accessibility', 'Laravel image fields SEO accessibility', ['Images', 'Accessibility', 'Laravel SEO'], 'Store featured image URLs, alt text, credits, and fallbacks for accessible SEO-friendly post cards.', 'treating images as content metadata, not decoration only', 'A post card uses alt text and a local fallback when an external image fails.'],
            ['Laravel Content Architecture for Topic Clusters', 'Laravel content architecture topic clusters', ['Content Architecture', 'SEO', 'Laravel'], 'Structure categories, tags, related posts, internal links, and hub pages for topic clusters.', 'making the codebase support long-term SEO publishing', 'A category page shows latest posts, related tags, and a clear intro.'],
            ['Laravel Production Checklist Before Launch', 'Laravel production checklist launch', ['Deployment', 'Security', 'Performance'], 'Review environment, debug mode, cache, queues, scheduler, logs, backups, SSL, and permissions before launch.', 'checking production basics before promotion', 'A developer runs optimize, verifies scheduler, and tests login before announcing the site.'],
        ];
    }

    private function businessTopics(): array
    {
        return [
            ['SaaS Ideas Morocco 2026: Practical Local Niches', 'SaaS ideas Morocco 2026', ['SaaS Ideas', 'Morocco', 'Startup Ideas'], 'A practical list of SaaS ideas for Moroccan local businesses, freelancers, education, bookings, and agencies.', 'finding SaaS ideas from repeated local business problems', 'A founder interviews three clinics before building appointment reminders.'],
            ['How to Find Clients as a Freelancer Without Spam', 'how to find clients freelancers 2026', ['Client Acquisition', 'Freelancing', 'Sales'], 'A client acquisition system based on positioning, useful outreach, referrals, content, and follow-up.', 'earning attention by solving specific problems', 'A developer sends a short website audit instead of a generic pitch.'],
            ['Web Agency Growth: Systems Before Hiring', 'web agency growth systems', ['Agency Growth', 'Operations', 'Client Delivery'], 'Grow a web agency with repeatable offers, onboarding, checklists, reporting, and delivery standards.', 'creating systems before adding people', 'An agency creates a launch checklist before hiring another developer.'],
            ['Freelance Pricing Guide for Digital Services', 'freelance pricing digital services 2026', ['Freelance Pricing', 'Digital Services', 'Client Work'], 'A practical pricing guide for web design, automation, content, SEO, and AI-assisted services.', 'pricing around outcomes, scope, risk, and delivery work', 'A freelancer adds discovery and revision limits to a fixed-price website offer.'],
            ['Client Acquisition for Developers and Designers', 'client acquisition developers designers', ['Client Acquisition', 'Developer Business', 'Freelancing'], 'How developers and designers can build a simple pipeline with portfolio proof, outreach, and follow-up.', 'creating a repeatable lead flow without relying on luck', 'A designer posts before-and-after redesign notes to attract similar clients.'],
            ['Digital Service Business Ideas for 2026', 'digital service business ideas 2026', ['Digital Services', 'Online Business', 'Freelancing'], 'Service ideas around websites, automation, AI workflows, analytics, content systems, and local business support.', 'selling practical outcomes instead of vague technical skills', 'A builder offers booking setup for local service businesses.'],
            ['Building a Portfolio That Sells Your Skills', 'building a portfolio that sells', ['Portfolio', 'Freelancing', 'Client Acquisition'], 'A portfolio structure that explains problems, process, outcomes, tools, and clear next steps.', 'turning proof into trust for buyers', 'A developer case study shows the old problem, the new workflow, and the result.'],
            ['Local Business Online Presence Checklist', 'local business online presence checklist', ['Local Business', 'Websites', 'Digital Services'], 'A checklist for local businesses covering website, Google profile, reviews, social proof, contact, and speed.', 'helping local businesses become easier to find and trust', 'A restaurant improves menu access, opening hours, and review links before running ads.'],
            ['Content Marketing for Freelancers in 2026', 'content marketing freelancers 2026', ['Content Marketing', 'Freelancing', 'SEO'], 'A content marketing system for freelancers who want inbound leads without publishing random posts.', 'creating proof, education, and discovery content around one niche', 'A freelancer writes weekly teardown posts for the exact clients they want.'],
            ['AI Business Ideas for Small Markets', 'AI business ideas small markets', ['AI Business', 'Startup Ideas', 'Automation'], 'Practical AI business ideas for local markets, service providers, education, admin work, and reporting.', 'using AI to improve boring workflows people already pay for', 'A founder sells AI-assisted report generation to a niche agency.'],
            ['How to Validate a SaaS Idea Before Coding', 'validate SaaS idea before coding', ['SaaS', 'Validation', 'Startup Ideas'], 'A validation workflow using interviews, manual service delivery, landing pages, and pre-sales signals.', 'testing demand before building the full product', 'A developer manually runs the workflow for two clients before writing the dashboard.'],
            ['How to Turn Freelance Work Into Productized Services', 'turn freelance work into productized services', ['Productized Services', 'Freelancing', 'Operations'], 'Package repeated freelance work into clear offers with scope, price, timeline, and delivery process.', 'making service delivery easier to buy and easier to repeat', 'A web developer turns custom landing pages into a fixed launch package.'],
            ['Online Business 2026: Helpful Assets Before Ads', 'online business helpful assets before ads', ['Online Business', 'Content Strategy', 'SEO'], 'Build durable assets like guides, tools pages, newsletters, and templates before spending on ads.', 'creating trust and search demand before paid promotion', 'A blog publishes category hubs before testing affiliate campaigns.'],
            ['How to Write Service Pages That Convert', 'service pages that convert 2026', ['Digital Services', 'Copywriting', 'Client Acquisition'], 'A service page framework with problem, offer, proof, process, pricing signals, FAQs, and CTA.', 'making the buying decision clear for serious clients', 'An agency page explains deliverables and timeline before asking for a call.'],
            ['Agency Reporting System for Better Client Retention', 'agency reporting system client retention', ['Agency Growth', 'Client Retention', 'Reporting'], 'A simple reporting system that shows work done, results, blockers, and next actions.', 'keeping clients confident with consistent communication', 'An agency sends a monthly report with metrics and plain-English notes.'],
            ['Digital Products for Freelancers: What to Build First', 'digital products freelancers build first', ['Digital Products', 'Freelancing', 'Online Income'], 'Beginner-friendly digital product ideas based on templates, checklists, calculators, and workflow packs.', 'turning repeated client knowledge into a small product', 'A freelancer sells a proposal checklist before building a full course.'],
            ['How to Use Testimonials and Case Studies Ethically', 'testimonials case studies ethically', ['Trust', 'Portfolio', 'Client Acquisition'], 'Use testimonials, screenshots, and case studies with permission, context, and honest claims.', 'building proof without exaggeration', 'A consultant asks permission before sharing a client result publicly.'],
            ['Newsletter Strategy for a Niche Online Business', 'newsletter strategy niche online business', ['Newsletter', 'Content Marketing', 'Online Business'], 'A simple newsletter strategy for topic clusters, trust, repeat readers, and product validation.', 'building an owned audience around useful expertise', 'A Laravel blog sends weekly summaries of finance, AI, and business guides.'],
        ];
    }

    private function buildPosts(string $category, array $topics, array $images): array
    {
        return collect($topics)->map(function (array $topic, int $index) use ($category, $images): array {
            [$title, $primaryKeyword, $tags, $excerpt, $angle, $example] = $topic;
            $image = $images[$index % count($images)];

            return [
                'category' => $category,
                'title' => $title,
                'slug' => Str::slug($title),
                'seo_title' => Str::limit($title.' | Youssef Blog', 68, ''),
                'excerpt' => $excerpt,
                'meta_description' => Str::limit($excerpt.' Practical 2026 guide with steps, examples, FAQs, and internal links.', 158, ''),
                'keywords' => collect([$primaryKeyword, $category.' 2026', ...$tags, 'Youssef Blog', 'builders guide'])->map(fn (string $keyword): string => Str::lower($keyword))->unique()->take(10)->values()->all(),
                'tags' => $tags,
                'image' => $image['url'],
                'image_alt' => $image['alt'],
                'image_credit' => $image['credit'],
                'angle' => $angle,
                'example' => $example,
                'primary_keyword' => $primaryKeyword,
            ];
        })->all();
    }

    private function content(array $data): string
    {
        $related = $this->relatedSuggestions($data['category']);

        return collect([
            '## Introduction',
            "{$data['title']} is a 2026-focused guide for builders who want practical progress without hype. The internet is full of shortcuts, but most useful results still come from clear decisions, consistent systems, and honest review.",
            "This article focuses on {$data['angle']}. It is written for beginners and early-stage builders, but it avoids shallow advice. You should finish with a workflow you can test this week, not just a list of ideas.",
            'The goal is not to promise traffic, income, or perfect outcomes. The goal is to improve your odds by making better choices, avoiding obvious mistakes, and connecting this topic with related guides on Youssef Blog.',
            '## Why this matters in 2026',
            'In 2026, tools are cheaper, competition is stronger, and attention is harder to earn. That combination rewards people who can build simple systems and keep improving them. Whether the topic is finance, tech, AI, Laravel, or business, the pattern is similar: define the outcome, choose the right constraints, and review what actually happened.',
            'A strong system also protects you from impulse decisions. You do not need to buy every tool, accept every client, publish every idea, or automate every task. You need a practical filter for deciding what helps and what creates noise.',
            "For this topic, the practical filter is simple: does the action support {$data['angle']} in a way you can maintain for at least four weeks?",
            '## The practical framework',
            'Start by writing one sentence that describes the problem. A vague goal creates vague work. A clear problem creates better decisions. For example, instead of saying you want to be more productive, say you want to reduce the time spent switching between client messages, notes, and delivery tasks.',
            'Next, list your current constraints. These may include budget, time, skill level, tools, language, location, or client expectations. Constraints are not a weakness. They help you choose the smallest useful next step.',
            'Then choose one workflow. A workflow is stronger than a random tip because it can be repeated. It should include the trigger, the action, the tool, the review step, and the decision you will make afterward.',
            '### Example',
            $data['example'],
            'The lesson from this example is not the exact tool or tactic. The lesson is that practical progress usually starts with a small controlled test. You want proof from your own context before you scale the idea.',
            '## Step-by-step checklist',
            '1. Define the outcome in one sentence so the work has a clear target.',
            '2. Choose one tool, template, or routine that supports the outcome.',
            '3. Set a small budget or time limit before you begin.',
            '4. Test the workflow on one project, client, article, or week of activity.',
            '5. Write down what improved, what became confusing, and what should be removed.',
            '6. Keep the parts that saved time, reduced risk, or improved quality.',
            '7. Link the workflow to a related habit so it does not disappear after one attempt.',
            '## Common mistakes to avoid',
            'The first mistake is copying someone else without adapting the advice. A student, freelancer, developer, agency owner, and SaaS founder do not have the same constraints. You can learn from examples, but your implementation should match your situation.',
            'The second mistake is measuring the wrong thing. Vanity metrics can feel exciting, but they do not always show whether the system is working. Useful measurements are often simple: time saved, money protected, errors reduced, client replies improved, pages published, or tasks completed.',
            'The third mistake is adding complexity too early. Many people install tools, create dashboards, and automate tasks before the manual process is clear. Manual clarity should come before automation.',
            '## Practical review routine',
            'At the end of the week, review the workflow with three questions. What became easier? What still created friction? What should be removed before the next attempt? This short review keeps the system honest. It also prevents you from confusing activity with progress, which is one of the easiest mistakes to make when learning a new tool, money habit, technical setup, or business process.',
            '## Internal link suggestions',
            "To build a stronger topic cluster, connect this article with related Youssef Blog guides about {$related[0]}, {$related[1]}, and {$related[2]}. These internal links help readers continue naturally and help search engines understand the relationship between your content hubs.",
            'When this post is live, add contextual links inside the introduction, practical framework, and conclusion. Avoid forcing links into every paragraph. A good internal link should answer the reader question that appears next.',
            '## FAQ',
            "### Is {$data['primary_keyword']} still useful in 2026?",
            'Yes, if you apply it to a real problem and measure whether it improves the outcome. The useful version is specific, tested, and maintained. The weak version is just a trend copied without context.',
            '### Who should use this guide?',
            'This guide is for beginners, freelancers, developers, creators, students, and small business owners who want practical steps. Advanced readers can still use it as a checklist for simplifying their current system.',
            '### What should I do after reading?',
            'Pick one step from the checklist and test it for seven days. After the test, decide whether to keep, simplify, or remove the workflow. Then read a related guide to strengthen the next part of the system.',
            '## Conclusion',
            "{$data['title']} works best when it becomes a repeatable system. You do not need a perfect plan today. You need a clear next step, a small test, and enough discipline to review the result honestly.",
            "CTA: Continue with another {$data['category']} guide on Youssef Blog, especially one connected to {$related[0]} or {$related[1]}, and build your 2026 system one useful workflow at a time.",
        ])->implode("\n\n");
    }

    private function faqs(array $data): array
    {
        return [
            ['question' => "Is {$data['primary_keyword']} still useful in 2026?", 'answer' => 'Yes, when it is applied to a specific problem, tested in a real workflow, and reviewed honestly.'],
            ['question' => 'Who should use this guide?', 'answer' => 'It is written for beginners, freelancers, developers, creators, students, and small business owners who want practical steps.'],
            ['question' => 'What should I do after reading?', 'answer' => 'Choose one checklist step, test it for seven days, and then continue with a related Youssef Blog guide.'],
        ];
    }

    private function relatedSuggestions(string $category): array
    {
        return match ($category) {
            'Finance' => ['budgeting for freelancers', 'online income ideas', 'finance apps and cash flow'],
            'Tech' => ['developer tools', 'hosting comparisons', 'website speed optimization'],
            'AI' => ['ChatGPT workflows', 'AI agents for small business', 'AI automation ideas'],
            'Laravel' => ['Laravel SEO', 'Laravel deployment', 'Laravel performance'],
            'Business' => ['client acquisition', 'SaaS ideas Morocco', 'content marketing'],
            default => ['finance', 'tech', 'AI'],
        };
    }

    private function featuredSlugs(): array
    {
        return [
            'budgeting-for-freelancers-in-morocco-in-2026',
            'online-income-ideas-that-fit-beginners-in-morocco',
            'best-laptops-for-developers-in-2026-what-matters-most',
            'best-vps-hosting-for-laravel-projects-in-2026',
            'best-ai-tools-2026-practical-stack-for-builders',
            'ai-agents-for-small-businesses-where-to-start',
            'laravel-seo-guide-2026-for-blade-blogs',
            'laravel-deployment-on-ubuntu-practical-checklist',
            'saas-ideas-morocco-2026-practical-local-niches',
            'how-to-find-clients-as-a-freelancer-without-spam',
        ];
    }

    private function images(): array
    {
        return [
            'finance' => [
                ['url' => 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Money planning with calculator and notebook', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Financial documents and calculator on a desk', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Person reviewing financial paperwork', 'credit' => 'Unsplash'],
            ],
            'tech' => [
                ['url' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Developer workstation with code editor', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Server racks in a data center', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Laptop showing code on a desk', 'credit' => 'Unsplash'],
            ],
            'ai' => [
                ['url' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Artificial intelligence network visualization', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?auto=format&fit=crop&w=1400&q=80', 'alt' => 'AI interface with abstract data lights', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Business team planning automation workflow', 'credit' => 'Unsplash'],
            ],
            'laravel' => [
                ['url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Code editor for web development', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Servers for Laravel hosting', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Circuit board representing secure infrastructure', 'credit' => 'Unsplash'],
            ],
            'business' => [
                ['url' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Freelancers working together on laptops', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Business planning session with laptop and notes', 'credit' => 'Unsplash'],
                ['url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80', 'alt' => 'Analytics dashboard for online business', 'credit' => 'Unsplash'],
            ],
        ];
    }
}
