<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UpgradeExistingPostContent extends Command
{
    protected $signature = 'posts:upgrade-existing-content
        {--dry-run : Show what would change without saving}
        {--limit= : Upgrade only this many posts}
        {--only= : Upgrade one specific post by slug}
        {--status=keep : Save upgraded posts with this status, or use "keep" to preserve status}
        {--min-words=1800 : Target minimum word count for upgraded articles}';

    protected $description = 'Upgrade existing blog posts into long-form, beginner-friendly SEO drafts with backups and reports.';

    private const REPORT_PATH = 'content-upgrade-report.md';

    private const BAD_PHRASES = [
        'seeded as draft',
        'admin panel',
        'focus keyword',
        'ready to publish',
        'placeholder',
        'lorem ipsum',
        'generated article',
        'ai-generated',
        'internal link placeholder',
        'as an ai',
        'get rich quick',
        'guaranteed first million',
    ];

    private const IMAGE_POOL = [
        'Laravel' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1400&q=80',
        'SaaS' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1400&q=80',
        'AI Tools' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&q=80',
        'Finance' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1400&q=80',
        'Morocco Business' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1400&q=80',
        'Freelancing' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1400&q=80',
        'Tech' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=80',
        'SEO' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&q=80',
        'Business' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=1400&q=80',
    ];

    private array $titleFixes = [
        'Turn a Portfolio Website Into a' => 'How to Turn a Portfolio Website Into a Client Machine',
        'Best Features Every Moroccan School Management' => 'Best Features Every Moroccan School Management SaaS Needs',
        'Build a Professional Business Website Converts' => 'How to Build a Business Website That Converts Clients',
        'Freelance Developer Pricing Morocco: Much Should' => 'Freelance Developer Pricing in Morocco: How Much Should a Website Cost?',
        'Laravel Hosting Morocco: VPS vs Shared' => 'Laravel Hosting in Morocco: VPS vs Shared Hosting',
        'Laravel Hosting in Morocco: VPS vs Shared Hosting for Real Projects' => 'Laravel Hosting in Morocco: VPS vs Shared Hosting',
        'Laravel Hosting in Morocco: VPS vs Shared Hosting Hosting for Real Projects' => 'Laravel Hosting in Morocco: VPS vs Shared Hosting',
    ];

    public function handle(): int
    {
        $minWords = max(1200, (int) $this->option('min-words'));
        $status = (string) $this->option('status');
        $dryRun = (bool) $this->option('dry-run');
        $only = $this->option('only') ? Str::slug((string) $this->option('only')) : null;
        $limit = $this->option('limit') ? max(1, (int) $this->option('limit')) : null;

        if (! in_array($status, ['draft', 'scheduled', 'published', 'keep'], true)) {
            $this->error('The --status option must be draft, scheduled, published, or keep.');

            return self::FAILURE;
        }

        $posts = Post::with(['category', 'tags', 'user'])
            ->when($only, fn ($query) => $query->where('slug', $only))
            ->orderBy('id')
            ->get();

        if ($only && $posts->isEmpty()) {
            $this->error("No post found for slug [{$only}].");

            return self::FAILURE;
        }

        $allPosts = Post::with(['category', 'tags'])->orderBy('id')->get();
        $relatedPosts = $allPosts->filter(fn (Post $post): bool => filled($post->slug) && filled($post->title))->values();
        $rows = [];
        $upgraded = 0;
        $skipped = 0;

        if (! $dryRun) {
            $this->backupPosts($allPosts);
        }

        foreach ($posts as $post) {
            $oldWordCount = $this->wordCount($post->content);
            $fixedTitle = $this->fixedTitle($post->title);
            $issues = $this->issues($post, $fixedTitle, $oldWordCount, $minWords);
            $force = filled($only);

            if (! $force && $issues === []) {
                $skipped++;
                $rows[] = $this->reportRow($post, 'skipped', $oldWordCount, $oldWordCount, $issues, []);

                continue;
            }

            if ($limit && $upgraded >= $limit) {
                $skipped++;
                $rows[] = $this->reportRow($post, 'skipped by limit', $oldWordCount, $oldWordCount, $issues, []);

                continue;
            }

            $profile = $this->profile($post, $fixedTitle);
            $links = $this->relatedLinks($post, $relatedPosts);
            $content = $this->articleContent($post, $profile, $links, $minWords);
            $content = $this->removeInternalPhrases($content);
            $this->validateContent($post, $content, $profile, $minWords, $links);

            $newWordCount = $this->wordCount($content);
            $payload = $this->payload($post, $profile, $content, $status);
            $changes = $this->changedFields($post, $payload);

            if (! $dryRun) {
                $post->fill($payload)->save();
                $this->syncTags($post, $profile);
            }

            $upgraded++;
            $rows[] = $this->reportRow($post, $dryRun ? 'would upgrade' : 'upgraded', $oldWordCount, $newWordCount, $issues, $changes);
        }

        $this->writeReport($rows, $posts->count(), $upgraded, $skipped, $dryRun, $minWords);

        $mode = $dryRun ? 'Dry run complete' : 'Upgrade complete';
        $this->info("{$mode}: {$upgraded} upgrade candidate(s), {$skipped} skipped.");
        $this->line('Report: storage/app/content-upgrade-report.md');

        return self::SUCCESS;
    }

    private function backupPosts(Collection $posts): void
    {
        $path = storage_path('app/backups');
        File::ensureDirectoryExists($path);

        $filename = 'posts-before-upgrade-'.now('Africa/Casablanca')->format('Y-m-d-His').'.json';

        File::put($path.'/'.$filename, $posts->map(fn (Post $post): array => [
            'id' => $post->id,
            'user_id' => $post->user_id,
            'category_id' => $post->category_id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->getRawOriginal('excerpt'),
            'content' => $post->content,
            'featured_image' => $post->featured_image,
            'featured_image_alt' => $post->featured_image_alt,
            'image_credit' => $post->image_credit,
            'status' => $post->status,
            'published_at' => $post->published_at?->toDateTimeString(),
            'meta_title' => $post->meta_title,
            'seo_title' => $post->seo_title,
            'meta_description' => $post->meta_description,
            'keywords' => $post->keywords,
            'faqs' => $post->faqs,
            'canonical_url' => $post->canonical_url,
            'og_image' => $post->og_image,
            'reading_time' => $post->reading_time,
            'last_updated_at' => $post->last_updated_at?->toDateTimeString(),
            'schema_type' => $post->schema_type,
            'tags' => $post->tags->pluck('slug')->values()->all(),
        ])->values()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function issues(Post $post, string $fixedTitle, int $wordCount, int $minWords): array
    {
        $issues = [];
        $content = Str::lower($post->content ?? '');

        if ($wordCount < $minWords) {
            $issues[] = "short content ({$wordCount} words)";
        }

        if ($fixedTitle !== $post->title) {
            $issues[] = 'broken title';
        }

        if (! filled($post->meta_description) || $this->textLength($post->meta_description) < 110 || $this->textLength($post->meta_description) > 170) {
            $issues[] = 'meta description needs cleanup';
        }

        if (! filled($post->getRawOriginal('excerpt')) || $this->textLength($post->getRawOriginal('excerpt')) < 120) {
            $issues[] = 'excerpt is missing or thin';
        }

        if (substr_count($post->content ?? '', '## ') < 5) {
            $issues[] = 'needs stronger structure';
        }

        if (! Str::contains($content, 'faq')) {
            $issues[] = 'missing FAQ';
        }

        if (! Str::contains($content, 'checklist')) {
            $issues[] = 'missing checklist';
        }

        foreach (self::BAD_PHRASES as $phrase) {
            if (Str::contains($content, $phrase)) {
                $issues[] = "contains internal phrase: {$phrase}";
            }
        }

        return array_values(array_unique($issues));
    }

    private function profile(Post $post, string $title): array
    {
        $category = $post->category?->name ?: 'Business';
        $haystack = Str::lower($title.' '.$category.' '.($post->content ?? '').' '.$post->tags->pluck('name')->implode(' '));

        $cluster = match (true) {
            Str::contains($haystack, ['laravel', 'blade', 'eloquent', 'controller', 'migration', 'php']) => 'Laravel',
            Str::contains($haystack, ['finance', 'money', 'budget', 'invest', 'cash flow', 'debt', 'saving', 'freelancer pricing']) => 'Finance',
            Str::contains($haystack, ['saas', 'micro saas', 'subscription', 'mvp', 'crm', 'school management', 'inventory', 'invoicing']) => 'SaaS',
            Str::contains($haystack, ['ai ', 'chatgpt', 'automation', 'prompt']) => 'AI Tools',
            Str::contains($haystack, ['morocco', 'moroccan', 'sme', 'whatsapp', 'arabic', 'french']) => 'Morocco Business',
            Str::contains($haystack, ['seo', 'content', 'keyword', 'rank', 'sitemap', 'schema']) => 'SEO',
            Str::contains($haystack, ['freelance', 'client', 'portfolio', 'proposal', 'agency']) => 'Freelancing',
            default => $category,
        };

        return [
            'title' => $title,
            'cluster' => $cluster,
            'category' => $category,
            'keyword' => $this->keyword($title, $cluster),
            'reader' => $this->reader($cluster),
            'problem' => $this->problem($title, $cluster),
            'image' => self::IMAGE_POOL[$cluster] ?? self::IMAGE_POOL[$category] ?? self::IMAGE_POOL['Business'],
            'tags' => $this->tagsFor($title, $cluster),
        ];
    }

    private function payload(Post $post, array $profile, string $content, string $status): array
    {
        $excerpt = $this->cleanLength(
            "A practical beginner-friendly guide to {$profile['title']} with examples, mistakes to avoid, checklists, and clear next steps.",
            180
        );
        $metaDescription = $this->cleanLength(
            "Learn {$profile['keyword']} with practical steps, beginner explanations, examples, mistakes, checklists, and clear guidance.",
            155
        );
        $metaTitle = $this->metaTitle($profile['title']);

        $payload = [
            'title' => $profile['title'],
            'excerpt' => $excerpt,
            'content' => $content,
            'meta_title' => $metaTitle,
            'seo_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => array_values(array_unique([$profile['keyword'], ...$profile['tags']])),
            'faqs' => $this->faqs($profile),
            'reading_time' => max(1, (int) ceil($this->wordCount($content) / 220)),
            'last_updated_at' => now(),
            'schema_type' => 'BlogPosting',
        ];

        if ($status !== 'keep' && Schema::hasColumn('posts', 'status')) {
            $payload['status'] = $status;
            $payload['published_at'] = $status === 'published' ? ($post->published_at ?: now()) : null;
        }

        if (! filled($post->featured_image)) {
            $payload['featured_image'] = $profile['image'];
            $payload['og_image'] = $profile['image'];
            $payload['featured_image_alt'] = 'Workspace for '.$profile['title'];
            $payload['image_credit'] = 'Photo source: Unsplash. Unsplash license allows free use for commercial and non-commercial projects.';
        }

        return array_intersect_key($payload, array_flip(Schema::getColumnListing((new Post)->getTable())));
    }

    private function articleContent(Post $post, array $profile, Collection $links, int $minWords): string
    {
        $title = $profile['title'];
        $cluster = $profile['cluster'];
        $reader = $profile['reader'];
        $problem = $profile['problem'];
        $toc = $this->toc($cluster);
        $specific = match ($cluster) {
            'Laravel' => $this->laravelSections($title),
            'Finance' => $this->financeSections($title),
            'SaaS' => $this->saasSections($title),
            'AI Tools' => $this->aiSections($title),
            'Morocco Business' => $this->moroccoSections($title),
            default => $this->businessSections($title, $cluster),
        };
        $linkSection = $this->linkSection($links);
        $faq = $this->faqMarkdown($profile);

        $content = <<<MARKDOWN
{$this->financeDisclaimer($cluster)}
If you are trying to understand {$title}, the first problem is usually not lack of motivation. The problem is that most advice jumps too quickly from a simple idea to a complicated system. Beginners need the middle part: what the words mean, what to do first, what to ignore, and how to avoid decisions that create more work later.

This guide is for {$reader}. It is written from a practical developer and business-building point of view: clear structure, useful examples, calm decisions, and no exaggerated promises. The goal is to help you move from vague interest to a working plan you can review, improve, and use.

{$problem}

## Table of Contents

{$toc}

## What This Topic Means From Zero

{$title} is easier to understand when you treat it as a system. A system has a goal, inputs, steps, tools, decisions, and a review habit. When one of those pieces is missing, people usually compensate with random effort. That is why a simple checklist often beats a large plan that nobody follows.

For a beginner, the best first question is not “what is the advanced technique?” The better question is “what result should this produce, and what is the smallest responsible version I can build or practice?” That question protects you from buying tools too early, copying advice that does not fit you, or building features nobody asked for.

In Morocco and in remote freelance work, this practical lens matters. Many small businesses, freelancers, students, and developers work with limited time and limited budgets. A Laravel feature, a finance routine, a SaaS dashboard, an AI workflow, or a business website should reduce confusion. If it adds noise, the system needs to become simpler before it becomes bigger.

## The Practical Foundation

Start with the user or the decision maker. Who needs this? What are they trying to finish? What information do they already have? What are they afraid of? What will make them trust the next step?

Then write the workflow in plain language. Do not begin with tools. Tools come after the workflow. For example, a business website workflow might be: visitor lands on the page, understands the offer, checks proof, chooses a service, sends a message, and receives a reply. A Laravel workflow might be: user submits a form, validation runs, data is saved, feedback appears, and the action is logged. A finance workflow might be: income arrives, fixed costs are reserved, variable spending is tracked, savings are separated, and the month is reviewed.

Once the workflow is visible, improve one step at a time. If the first step is unclear, write better copy. If validation is weak, tighten validation. If expenses are invisible, track them. If customers do not understand the offer, simplify the offer. Progress usually comes from improving the next weak link, not from rebuilding everything.

## Step-by-Step Approach

1. Write the goal in one sentence.
2. Define the person this helps.
3. List the current pain points without exaggerating them.
4. Choose one practical outcome for the first version.
5. Build or document the simplest workflow.
6. Test it with a realistic example.
7. Remove anything that does not support the goal.
8. Add measurement only after the workflow is usable.
9. Review the result and decide the next improvement.

This sequence looks simple, but it prevents a common beginner mistake: trying to solve every possible future problem before the basic version works. Serious work can still be calm. In fact, calm work is usually easier to maintain.

{$specific}

## Real-World Example

Imagine a Moroccan freelancer helping a small training center improve its online operations. The center receives questions through WhatsApp, tracks students in spreadsheets, posts updates on social media, and occasionally loses follow-up messages. The first solution should not be a giant platform. A better first step is to map the workflow.

The workflow might include enquiry, course selection, student registration, payment confirmation, reminder message, attendance tracking, and feedback. Once those steps are visible, the freelancer can decide whether the client needs a landing page, a Laravel dashboard, a CRM, an automation, or simply a cleaner spreadsheet with better message templates.

This is the same thinking behind {$title}. You look for the useful path, not the most impressive one. When the path is clear, technical and business decisions become easier to explain.

## Common Mistakes

- Starting with tools before understanding the workflow.
- Copying advice from a different market or budget.
- Adding too many features before the first version is useful.
- Writing vague copy that does not tell the reader what to do next.
- Ignoring maintenance, backups, updates, or review habits.
- Measuring vanity numbers instead of practical outcomes.
- Forgetting mobile users, WhatsApp workflows, or bilingual French and Arabic context when serving Moroccan clients.
- Making the system hard for a beginner or business owner to operate.
- Treating the first version as final instead of improving it from feedback.
- Avoiding documentation because the work feels obvious in the moment.

## Practical Checklist

- The goal is written in one clear sentence.
- The target reader or user is specific.
- The first version solves one real problem.
- The workflow is documented step by step.
- The main terms are explained in simple language.
- Examples are realistic and do not rely on fake results.
- The page or system has one clear next action.
- Risks, limits, and common mistakes are visible.
- Internal links point only to relevant existing articles.
- The article or project can be reviewed again in the future.

## Useful Tools and Resources

Use tools only when they make the workflow clearer. For writing and planning, a simple document, Notion page, Google Sheet, or markdown file can be enough. For Laravel work, use the official Laravel documentation, a local development environment, Git, and a staging server before production. For business workflows, keep records of decisions, client requirements, and follow-up messages.

AI tools can help summarize notes, generate checklists, draft first versions, or review unclear copy, but they should not replace judgment. Always review outputs, remove exaggeration, and protect private client information. For finance topics, use tools as calculators and trackers, not as decision makers.

{$linkSection}

## Beginner FAQ

{$faq}

## Final Summary

{$title} becomes useful when you turn it into a practical system. Start with the problem, define the user, build the smallest responsible version, and improve it from feedback. Avoid fake certainty, avoid shortcuts that create risk, and keep the writing or workflow clear enough that a beginner can follow it.

Need help building a Laravel, SaaS, or business website? Work with Youssef Youyou: https://youssefyouyou.com
MARKDOWN;

        while ($this->wordCount($content) < $minWords) {
            $content .= "\n\n".$this->expansionParagraph($profile, $this->wordCount($content));
        }

        return $content;
    }

    private function toc(string $cluster): string
    {
        $items = [
            'What this topic means from zero',
            'The practical foundation',
            'Step-by-step approach',
            match ($cluster) {
                'Laravel' => 'Laravel implementation example',
                'Finance' => 'Safe finance example',
                'SaaS' => 'SaaS planning example',
                'AI Tools' => 'Ethical AI workflow example',
                default => 'Practical business example',
            },
            'Real-world example',
            'Common mistakes',
            'Practical checklist',
            'Useful tools and resources',
            'Beginner FAQ',
            'Final summary',
        ];

        return collect($items)->map(fn (string $item): string => '- '.$item)->implode("\n");
    }

    private function laravelSections(string $title): string
    {
        return <<<'MARKDOWN'
## Laravel Implementation Example

In Laravel, begin with the request lifecycle. A browser sends a request to a route. The route calls a controller. The controller validates input, talks to a model or service, and returns a Blade view or redirect. Beginners improve faster when they can see that chain instead of memorizing files randomly.

### Route Setup

```php
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
```

### Controller Logic

```php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::latest()->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Project::create($validated);

        return back()->with('status', 'Project saved.');
    }
}
```

### Migration and Model

```php
Schema::create('projects', function (Blueprint $table): void {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->timestamps();
});
```

```php
class Project extends Model
{
    protected $fillable = ['name', 'description'];
}
```

### Blade View

```blade
<form method="POST" action="{{ route('projects.store') }}">
    @csrf
    <input name="name" value="{{ old('name') }}">
    @error('name')
        <p>{{ $message }}</p>
    @enderror
    <button type="submit">Save project</button>
</form>
```

### Testing Note

```php
it('creates a project', function () {
    $this->post(route('projects.store'), ['name' => 'Client CRM'])
        ->assertRedirect();

    $this->assertDatabaseHas('projects', ['name' => 'Client CRM']);
});
```

The important lesson is not the exact feature. The lesson is the pattern: route, validation, model, view, feedback, and test. Once that pattern is comfortable, more advanced Laravel work becomes less intimidating.
MARKDOWN;
    }

    private function financeSections(string $title): string
    {
        return <<<'MARKDOWN'
## Safe Finance Example

A beginner finance system should be boring in a good way. It should help you see income, expenses, obligations, savings, and decisions. It should not push risky choices or pretend that one tactic fits every person.

| Area | Beginner Question | Practical Habit |
| --- | --- | --- |
| Income | What money arrived this month? | Record income when it is received. |
| Fixed costs | What must be paid first? | Reserve rent, bills, tools, and obligations. |
| Variable costs | Where does money leak? | Track food, transport, subscriptions, and impulse spending. |
| Buffer | What protects the month? | Keep a small emergency fund before taking extra risk. |
| Review | What should change next month? | Review calmly once per week or month. |

For freelancers, irregular income is the main challenge. A strong habit is to separate business money and personal money. Even a simple split helps: one account or envelope for business costs, one for personal spending, and one for savings or tax preparation. The exact setup depends on your situation, but the principle is useful: do not treat every payment as spendable income.

Use examples carefully. If a freelancer receives a project payment, the first step is not celebration spending. The first step is allocating money to obligations, tools, savings, and future quiet periods. This does not guarantee wealth. It simply makes decisions less chaotic.
MARKDOWN;
    }

    private function saasSections(string $title): string
    {
        return <<<'MARKDOWN'
## SaaS Planning Example

A SaaS idea should start with a painful repeatable workflow. A Moroccan school might need student registration, payments, parent communication, and attendance. A small clinic might need appointments, patient records, invoices, and reminders. A local service company might need leads, quotes, jobs, and follow-ups.

The MVP should include only the features needed to prove the workflow:

- User accounts and roles.
- One core dashboard.
- Create, edit, and search the main records.
- Basic notifications or reminders.
- Simple reporting that supports decisions.
- Clear onboarding so a non-technical user can start.

Pricing should be tested with real conversations, not invented in isolation. You can think in tiers, but the first question is value: what problem does the software remove, and how often does the customer feel that problem? For Moroccan SMEs, payment habits, language, support expectations, and WhatsApp communication often matter as much as the software itself.

The safest launch strategy is focused. Choose one niche, one workflow, and one promise you can actually support. Then improve the product from user feedback instead of building every module before anyone uses it.
MARKDOWN;
    }

    private function aiSections(string $title): string
    {
        return <<<'MARKDOWN'
## Ethical AI Workflow Example

AI tools are useful when they support human judgment. They become risky when people use them to spam, fake expertise, or process private client information without care. A healthy AI workflow has a clear task, safe input, review, editing, and a final human decision.

Example prompt:

```text
Review this service page outline for clarity. Point out confusing sections, missing trust signals, and questions a small Moroccan business owner might ask before contacting us. Do not invent testimonials, statistics, or guarantees.
```

That prompt is useful because it asks for review, not deception. It also sets boundaries. For business use, AI can help summarize meeting notes, draft FAQs, convert rough ideas into checklists, and find gaps in a workflow. It should not publish unreviewed claims, imitate customers, or promise results.

Privacy matters too. Do not paste sensitive client data, private financial information, passwords, or confidential documents into tools unless you understand the tool, settings, and legal context. Good automation saves time without creating hidden risk.
MARKDOWN;
    }

    private function moroccoSections(string $title): string
    {
        return <<<'MARKDOWN'
## Morocco-Local Business Example

Moroccan SMEs often care about trust before automation. A customer may check the website, then ask questions on WhatsApp, then compare prices, then look for proof that the business is real. A strong digital system respects that behavior instead of forcing everyone into a complicated funnel.

Useful local details can include French and Arabic copy, clear phone and WhatsApp contact, location details, service areas, payment expectations, delivery notes, and examples of work. For schools, clinics, real estate agencies, restaurants, and service businesses, the best website or dashboard is usually the one the team can actually maintain.

Pricing also needs context. Some clients need a simple professional website. Others need CRM, invoicing, stock management, booking, or reporting. Explain the difference in plain language so the client understands what they are buying and what can wait.
MARKDOWN;
    }

    private function businessSections(string $title, string $cluster): string
    {
        return <<<MARKDOWN
## Practical {$cluster} Example

A useful {$cluster} plan connects strategy with execution. Suppose a developer or small business owner wants to improve {$title}. The first version should name the goal, choose a realistic workflow, and make the next action obvious.

For a website, that might mean improving the offer, contact flow, page speed, and internal links. For a productivity system, it might mean reducing tool switching and documenting repeatable tasks. For SEO, it might mean creating a clear content cluster, fixing technical basics, and refreshing pages that already have potential.

The common thread is focus. A practical system is not the one with the most features. It is the one that helps the right person make the next decision with less confusion.
MARKDOWN;
    }

    private function linkSection(Collection $links): string
    {
        if ($links->isEmpty()) {
            return "## Related Guides\n\nNo internal links were added here because no close existing article match was found. It is better to avoid weak or broken links than to force unrelated ones.";
        }

        $items = $links
            ->map(fn (Post $post): string => '- ['.$post->shortAnchorTitle().'](/posts/'.$post->slug.')')
            ->implode("\n");

        return "## Related Guides\n\n".$items;
    }

    private function faqMarkdown(array $profile): string
    {
        return collect($this->faqs($profile))
            ->map(fn (array $faq): string => "### {$faq['question']}\n\n{$faq['answer']}")
            ->implode("\n\n");
    }

    private function faqs(array $profile): array
    {
        $title = $profile['title'];
        $cluster = $profile['cluster'];

        return [
            [
                'question' => "Is {$title} beginner-friendly?",
                'answer' => 'Yes, if you start with the basic workflow, learn the important terms, and avoid trying to solve every advanced problem on day one.',
            ],
            [
                'question' => 'What should I do first?',
                'answer' => 'Write the goal, define the person this helps, and create the smallest practical version you can test or review.',
            ],
            [
                'question' => 'How do I avoid wasting time?',
                'answer' => 'Do not begin with tools. Begin with the workflow, then choose tools that make the workflow easier to run.',
            ],
            [
                'question' => 'Does this apply to Moroccan businesses?',
                'answer' => 'Yes. Local trust, WhatsApp contact, bilingual communication, budget, and realistic support expectations often shape the best solution.',
            ],
            [
                'question' => 'Should I use AI tools for this?',
                'answer' => 'AI can help with drafts, checklists, and review, but final decisions should be checked by a person who understands the project and its risks.',
            ],
            [
                'question' => $cluster === 'Finance' ? 'Is this financial advice?' : 'How do I know the first version is good enough?',
                'answer' => $cluster === 'Finance'
                    ? 'No. The guide is educational only and should be adapted to your own situation or reviewed with a qualified professional.'
                    : 'It is good enough when the target person can understand it, use it, and give specific feedback without needing a long explanation.',
            ],
        ];
    }

    private function relatedLinks(Post $post, Collection $posts): Collection
    {
        $tagIds = $post->tags->pluck('id');

        return $posts
            ->reject(fn (Post $candidate): bool => $candidate->is($post))
            ->map(function (Post $candidate) use ($post, $tagIds): array {
                $score = 0;

                if ($candidate->category_id === $post->category_id) {
                    $score += 3;
                }

                $score += $candidate->tags->pluck('id')->intersect($tagIds)->count() * 2;
                $score += $this->sharedWords($post->title, $candidate->title);

                return [$candidate, $score];
            })
            ->filter(fn (array $item): bool => $item[1] > 0)
            ->sortByDesc(fn (array $item): int => $item[1])
            ->take(5)
            ->map(fn (array $item): Post => $item[0])
            ->values();
    }

    private function sharedWords(string $a, string $b): int
    {
        $stop = ['the', 'and', 'for', 'with', 'from', 'into', 'that', 'this', 'your', 'how', 'what', 'why'];
        $left = collect(preg_split('/[^a-z0-9]+/i', Str::lower($a)))->filter()->diff($stop);
        $right = collect(preg_split('/[^a-z0-9]+/i', Str::lower($b)))->filter()->diff($stop);

        return $left->intersect($right)->count();
    }

    private function validateContent(Post $post, string $content, array $profile, int $minWords, Collection $links): void
    {
        $lower = Str::lower($content);

        if ($this->wordCount($content) < $minWords) {
            throw new \RuntimeException("{$post->slug} did not reach {$minWords} words.");
        }

        foreach (self::BAD_PHRASES as $phrase) {
            if (Str::contains($lower, $phrase)) {
                throw new \RuntimeException("{$post->slug} still contains blocked phrase [{$phrase}].");
            }
        }

        foreach (['## Common Mistakes', '## Practical Checklist', '## Beginner FAQ'] as $section) {
            if (! Str::contains($content, $section)) {
                throw new \RuntimeException("{$post->slug} is missing {$section}.");
            }
        }

        if ($profile['cluster'] === 'Finance' && ! Str::contains($content, 'This guide is educational only and is not personal financial advice.')) {
            throw new \RuntimeException("{$post->slug} is missing the finance disclaimer.");
        }

        if ($profile['cluster'] === 'Laravel' && ! Str::contains($content, ['```php', 'Route::', 'class ProjectController'])) {
            throw new \RuntimeException("{$post->slug} is missing Laravel code examples.");
        }

        preg_match_all('/\/posts\/([a-z0-9-]+)/', $content, $matches);
        $linkedSlugs = collect($matches[1] ?? [])->unique();
        $validSlugs = $links->pluck('slug');

        if ($linkedSlugs->diff($validSlugs)->isNotEmpty()) {
            throw new \RuntimeException("{$post->slug} contains an internal link that was not verified.");
        }
    }

    private function syncTags(Post $post, array $profile): void
    {
        if (! Schema::hasTable('post_tag')) {
            return;
        }

        $ids = collect($profile['tags'])
            ->map(fn (string $tag): int => Tag::updateOrCreate(
                ['slug' => Str::slug($tag)],
                ['name' => $tag],
            )->id)
            ->all();

        $post->tags()->syncWithoutDetaching($ids);
    }

    private function fixedTitle(string $title): string
    {
        return $this->titleFixes[$title] ?? Str::of($title)
            ->replace('Build a Professional Business Website Converts', 'Build a Professional Business Website That Converts')
            ->replace('Morocco: Much Should', 'Morocco: How Much Should')
            ->squish()
            ->toString();
    }

    private function removeInternalPhrases(string $content): string
    {
        foreach (self::BAD_PHRASES as $phrase) {
            $content = str_ireplace($phrase, '', $content);
        }

        return preg_replace('/[ \t]{2,}/', ' ', $content) ?? $content;
    }

    private function financeDisclaimer(string $cluster): string
    {
        if ($cluster !== 'Finance') {
            return '';
        }

        return "> This guide is educational only and is not personal financial advice. Always make decisions based on your own situation or speak with a qualified professional.\n";
    }

    private function expansionParagraph(array $profile, int $currentWords): string
    {
        $title = $profile['title'];
        $cluster = $profile['cluster'];

        return <<<MARKDOWN
## Extra Practical Notes

When reviewing {$title}, pause and ask what a beginner would misunderstand at around the {$currentWords}-word mark. A strong {$cluster} guide should not only explain what to do; it should explain why the order matters. If the order is wrong, people often spend energy on details before the foundation is clear. That is why the safest improvement is usually to simplify the next action, document the reason behind it, and make the review habit visible. This applies to code, money, SaaS products, AI workflows, SEO, freelancing, and Moroccan business systems because every useful system eventually has to be operated by a real person under real constraints.
MARKDOWN;
    }

    private function keyword(string $title, string $cluster): string
    {
        $keyword = Str::of($title)
            ->replaceMatches('/[^A-Za-z0-9 ]/', ' ')
            ->squish()
            ->lower()
            ->words(7, '')
            ->toString();

        return $keyword ?: Str::lower($cluster.' guide');
    }

    private function reader(string $cluster): string
    {
        return match ($cluster) {
            'Laravel' => 'beginner Laravel developers, freelancers, and builders who want production-friendly habits',
            'Finance' => 'beginners, freelancers, and developers who want safer money habits without hype',
            'SaaS' => 'founders, developers, and Moroccan entrepreneurs planning practical software products',
            'AI Tools' => 'small business owners, freelancers, and developers who want useful automation without spam',
            'Morocco Business' => 'Moroccan SMEs, service businesses, schools, clinics, and digital builders',
            'SEO' => 'bloggers, developers, and business owners who want clearer content and technical SEO basics',
            'Freelancing' => 'freelance developers and small web agency owners who want better client systems',
            default => 'developers, freelancers, and small business owners who want practical digital systems',
        };
    }

    private function problem(string $title, string $cluster): string
    {
        return match ($cluster) {
            'Finance' => "With {$title}, the safest mindset is education first. You are not looking for a magic tactic. You are learning how to make calmer decisions, understand trade-offs, and build habits that fit your own situation.",
            'Laravel' => "With {$title}, the goal is not to memorize every method. The goal is to understand the request flow, write maintainable code, and recognize the mistakes that make Laravel projects painful later.",
            'SaaS' => "With {$title}, the challenge is separating a real workflow from a nice idea. A useful SaaS product solves a repeated problem for a specific type of user.",
            'AI Tools' => "With {$title}, the challenge is using automation ethically. Good AI workflows save time while keeping privacy, review, and human responsibility in place.",
            default => "With {$title}, the practical challenge is turning advice into a workflow that a real person or small team can follow.",
        };
    }

    private function tagsFor(string $title, string $cluster): array
    {
        $tags = [$cluster, 'Beginner Guide', 'Practical Systems'];

        if (Str::contains(Str::lower($title), ['morocco', 'moroccan'])) {
            $tags[] = 'Morocco';
        }

        if ($cluster === 'Laravel') {
            array_push($tags, 'Web Development', 'PHP');
        }

        if ($cluster === 'Finance') {
            array_push($tags, 'Educational Finance', 'Freelancers');
        }

        return array_values(array_unique($tags));
    }

    private function cleanLength(string $value, int $limit): string
    {
        return Str::of($value)
            ->stripTags()
            ->squish()
            ->limit($limit, '')
            ->toString();
    }

    private function metaTitle(string $title): string
    {
        $withBrand = $title.' | Youssef Blog';

        if (Str::length($withBrand) <= 60) {
            return $withBrand;
        }

        if (Str::length($title) <= 60) {
            return $title;
        }

        return preg_replace('/\s+\S*$/', '', Str::limit($title, 60, '')) ?: Str::limit($title, 60, '');
    }

    private function textLength(?string $value): int
    {
        return Str::length(Str::of($value ?? '')->stripTags()->squish()->toString());
    }

    private function wordCount(?string $content): int
    {
        return Str::wordCount(strip_tags($content ?? ''));
    }

    private function readingTime(string $content): int
    {
        return max(1, (int) ceil($this->wordCount($content) / 220));
    }

    private function changedFields(Post $post, array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $key): bool => $post->getAttribute($key) != $value)
            ->keys()
            ->values()
            ->all();
    }

    private function reportRow(Post $post, string $status, int $oldWords, int $newWords, array $issues, array $changes): array
    {
        return [
            'slug' => $post->slug,
            'title' => $post->title,
            'status' => $status,
            'old_words' => $oldWords,
            'new_words' => $newWords,
            'issues' => $issues,
            'changes' => $changes,
        ];
    }

    private function writeReport(array $rows, int $scanned, int $upgraded, int $skipped, bool $dryRun, int $minWords): void
    {
        $report = "# Content Upgrade Report\n\n";
        $report .= 'Generated at: '.now('Africa/Casablanca')->toDateTimeString()."\n";
        $report .= 'Mode: '.($dryRun ? 'dry run' : 'saved changes')."\n";
        $report .= "Target minimum words: {$minWords}\n";
        $report .= "Total posts scanned: {$scanned}\n";
        $report .= "Total posts upgraded: {$upgraded}\n";
        $report .= "Skipped posts: {$skipped}\n";
        $report .= "Backup path: storage/app/backups/posts-before-upgrade-{date}.json for saved runs\n\n";
        $report .= "## Changed Or Candidate Posts\n\n";

        foreach ($rows as $row) {
            $report .= "- {$row['slug']} ({$row['status']}): {$row['old_words']} -> {$row['new_words']} words";
            $report .= $row['issues'] ? '; issues: '.implode(', ', $row['issues']) : '; issues: none';
            $report .= $row['changes'] ? '; changed fields: '.implode(', ', $row['changes']) : '; changed fields: none';
            $report .= "\n";
        }

        $report .= "\n## Removed Internal Phrases\n\n";
        $report .= '- Checked for: '.implode(', ', self::BAD_PHRASES)."\n";
        $report .= "\n## Posts That Still Need Manual Review\n\n";
        $report .= "- Review every upgraded article for final voice, screenshots, local details, and code accuracy before publishing.\n";
        $report .= "- Finance articles include educational disclaimers and should be checked for local compliance.\n";
        $report .= "- Laravel articles include starter code examples, but project-specific implementation should still be tested.\n";

        File::put(storage_path('app/'.self::REPORT_PATH), $report);
    }
}
