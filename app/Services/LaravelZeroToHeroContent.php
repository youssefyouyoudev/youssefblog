<?php

namespace App\Services;

use Illuminate\Support\Str;

class LaravelZeroToHeroContent
{
    public const MIN_WORDS = 2500;

    public static function wordCount(string $content): int
    {
        return Str::wordCount(strip_tags($content));
    }

    /**
     * @return array<int, string>
     */
    public static function bannedPhrases(): array
    {
        return [
            'in today\'s digital landscape',
            'unlock the power',
            'game-changer',
            'revolutionize',
            'cutting-edge',
            'leverage',
            'skyrocket',
            'transform your business overnight',
            'let\'s explore',
            'dive into',
            'world-class',
            'guaranteed results',
            'look no further',
        ];
    }

    /**
     * @param  array<string, mixed>  $post
     */
    public function for(array $post, ?array $previous = null, ?array $next = null): string
    {
        $sections = collect($post['sections'])
            ->map(fn (array $section, int $index): string => $this->lessonSection($post, $section, $index))
            ->implode("\n\n");

        $content = <<<MARKDOWN
{$post['intro']}

This lesson is part of **{$post['phase']}** in the Laravel From Zero to Hero series. It focuses on {$post['search_intent']} and is written for developers who want to understand Laravel clearly enough to build useful projects: business websites, dashboards, admin panels, SaaS MVPs, APIs, portals, and custom tools.

The goal of {$post['title']} is practical understanding. You do not need to memorize every class name on the first pass. You need to understand the shape of the idea, where it appears in a real Laravel project, and which mistakes usually make beginner projects harder to maintain.

## Why this lesson matters

{$post['scenario']}

For SEO traffic, this topic usually attracts people searching for **{$post['search_intent']}**. For real learning, the better question is more specific: what should a beginner be able to build, debug, or explain after reading the lesson? That is the standard used here.

{$sections}

{$this->codeExample($post)}

## A real project way to think about it

When Laravel is used for a business project, {$post['search_intent']} is not learned for practice only. It supports a workflow. A contact form may start a sales conversation. A dashboard may help an owner understand overdue invoices. A queue may send emails without making the user wait. A policy may stop one user from opening another customer’s data.

That is why each Laravel concept should be connected to a real outcome. If you understand {$post['search_intent']} only as syntax, you may pass a tutorial but still struggle inside a client project. If you understand the business reason behind the syntax, the framework starts to feel calmer.

In a client portal, {$post['search_intent']} may affect trust. In a SaaS MVP, it may affect the first user experience. In a CRM, it may affect follow-up quality. In an e-commerce admin area, it may affect orders, stock, and customer support. Laravel gives you tools, but your job while working through {$post['title']} is to use them with judgment.

## Common mistakes to avoid

- Copying code without understanding which file owns the responsibility.
- Putting too much logic in one route, controller, Blade file, or model.
- Ignoring validation, authorization, or error handling because the example works on your machine.
- Naming variables, routes, tables, or methods too vaguely.
- Building the advanced version before the simple version is working and readable.
- Skipping tests or manual checks for the workflow the user actually depends on.

These mistakes are normal when you are learning {$post['search_intent']}. The important thing is to catch them early. A small Laravel project can survive messy code for a while. A real business application cannot stay healthy if every feature makes the structure harder to understand.

## Practice checklist

- Explain the concept in one sentence without using jargon.
- Find the related file or folder in a Laravel project.
- Build a small example that uses the concept once.
- Add validation or error handling if user input is involved.
- Ask what could go wrong in a real business workflow.
- Refactor the code so a future developer can read it quickly.
- Write down one question you still have before moving to the next lesson.

## How this connects to client work

Many people read {$post['title']} because they want a better job, freelance projects, or the ability to build their own product. That is a good reason to study Laravel seriously. Clients do not pay for syntax; they pay for working systems that solve problems with less confusion.

If you can explain {$post['search_intent']} in plain English, use it in a small feature, and avoid the common traps, you are already closer to building useful Laravel work. Over time, these lessons connect: routes lead to controllers, controllers use models, models use migrations, views display data, validation protects forms, policies protect actions, queues handle background work, and deployment brings the project to real users.

## Recap

{$this->recap($post)}

{$this->navigation($previous, $next)}

## Need help applying Laravel to a real project?

If you are learning {$post['search_intent']} because you want to build business websites, dashboards, SaaS MVPs, CRMs, APIs, or automation tools, I build practical Laravel systems around real business needs. You can see my work at https://youssefyouyou.com or contact me through https://youssefyouyou.com/contact.
MARKDOWN;

        return $this->expand($content, $post);
    }

    /**
     * @param  array<string, mixed>  $post
     * @param  array<int, string>  $section
     */
    private function lessonSection(array $post, array $section, int $index): string
    {
        [$heading, $body] = $section;
        $detail = $this->detailFor($post, $heading, $index);

        return <<<MARKDOWN
## {$heading}

{$body}

{$detail}

### What to try

- Open a Laravel project and find where this concept would live.
- Write a tiny example related to {$post['search_intent']}.
- Read the code back as if another developer will maintain it next month.
- Check whether the example would still make sense inside a dashboard, portal, API, or content website.
MARKDOWN;
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function detailFor(array $post, string $heading, int $index): string
    {
        $details = [
            "For {$post['title']}, this part matters because it shapes how the project behaves after the first happy-path demo. A beginner can often make the code work once. A better Laravel developer asks how the code will behave when the data is missing, the user makes a mistake, or the feature needs to change.",
            "Think about {$heading} as a maintainability decision. If the code is clear here, future work becomes easier. If it is rushed, later features inherit the confusion. That is especially true in client work, where a dashboard or SaaS feature rarely stays frozen after launch.",
            "A useful habit is to connect {$heading} to one business example. If the project is a CRM, what does this mean for leads? If it is a SaaS app, what does it mean for accounts? If it is an e-commerce admin area, what does it mean for orders or products?",
            'Do not judge this only by whether the page loads. Judge it by whether the next developer can understand the intent, whether the user gets useful feedback, and whether the application protects important data.',
        ];

        return $details[$index % count($details)];
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function codeExample(array $post): string
    {
        $snippets = [
            'what-is-laravel-why-popular' => <<<'MARKDOWN'
## Simple Laravel flow example

```php
use Illuminate\Support\Facades\Route;

Route::get('/welcome-client', function () {
    return view('welcome-client', [
        'name' => 'Youssef',
        'service' => 'Laravel dashboard development',
    ]);
});
```

This tiny route shows the basic Laravel idea: a request comes in, Laravel runs the matching code, and a response goes back to the browser. In real applications, the route usually calls a controller instead of doing the work directly.
MARKDOWN,
            'how-to-install-laravel-local-environment' => <<<'MARKDOWN'
## First commands to run

```bash
composer create-project laravel/laravel my-first-app
cd my-first-app
php artisan serve
php artisan about
```

The `about` command is useful because it shows the PHP version, Laravel version, environment, cache status, database connection, queue driver, and other details that help you debug setup problems.
MARKDOWN,
            'laravel-folder-structure-explained' => <<<'MARKDOWN'
## Where common files live

```text
routes/web.php              # browser routes
app/Http/Controllers        # controller classes
app/Models                  # Eloquent models
resources/views             # Blade templates
database/migrations         # database structure
tests/Feature               # request-level tests
```

When you are lost, ask what type of responsibility you are dealing with. A request belongs near routes and controllers. A database record belongs near models and migrations. A rendered page belongs near views.
MARKDOWN,
            'laravel-routing-explained-beginners' => <<<'MARKDOWN'
## Route to controller example

```php
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/posts/{post:slug}', [PostController::class, 'show'])
    ->name('posts.show');
```

This route accepts a post slug, asks Laravel to resolve the matching post, and sends the request to a controller method. Naming the route makes links easier to maintain.
MARKDOWN,
            'controllers-views-blade-templates-laravel' => <<<'MARKDOWN'
## Controller and Blade example

```php
public function show(Post $post)
{
    return view('posts.show', [
        'post' => $post,
    ]);
}
```

```blade
<h1>{{ $post->title }}</h1>
<p>{{ $post->excerpt }}</p>
```

The controller prepares data. The Blade file displays it safely. This separation keeps pages easier to understand.
MARKDOWN,
            'laravel-migrations-explained-real-examples' => <<<'MARKDOWN'
## Migration example

```php
Schema::create('leads', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->index();
    $table->string('status')->default('new');
    $table->timestamp('follow_up_at')->nullable();
    $table->timestamps();
});
```

This table is simple, but it already reflects a real workflow: a lead arrives, gets a status, and may need follow-up.
MARKDOWN,
            'eloquent-models-laravel-database' => <<<'MARKDOWN'
## Model example

```php
class Lead extends Model
{
    protected $fillable = ['name', 'email', 'status', 'follow_up_at'];

    protected function casts(): array
    {
        return ['follow_up_at' => 'datetime'];
    }
}
```

The model describes which fields can be filled and how values should behave in PHP. This keeps controllers cleaner.
MARKDOWN,
            'building-first-laravel-crud-feature' => <<<'MARKDOWN'
## Store action example

```php
public function store(Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email'],
    ]);

    Lead::create($data);

    return redirect()->route('leads.index')
        ->with('status', 'Lead created.');
}
```

This is the heart of many CRUD features: validate, save, redirect, and tell the user what happened.
MARKDOWN,
            'laravel-validation-handle-form-data' => <<<'MARKDOWN'
## Validation example

```php
$validated = $request->validate([
    'project_type' => ['required', 'string', 'max:80'],
    'budget_range' => ['nullable', 'string', 'max:80'],
    'email' => ['required', 'email'],
    'message' => ['required', 'string', 'min:20'],
]);
```

Validation turns a vague form into a clear contract. The application knows what it expects, and the user gets feedback when something is missing.
MARKDOWN,
            'eloquent-relationships-explained-simply' => <<<'MARKDOWN'
## Relationship example

```php
class Customer extends Model
{
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
```

```php
$customer = Customer::with('invoices')->findOrFail($id);
```

The relationship lets Laravel express the business rule directly: one customer can have many invoices.
MARKDOWN,
            'laravel-authentication-login-registration-users' => <<<'MARKDOWN'
## Protecting a dashboard route

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');
});
```

The middleware ensures guests cannot access the dashboard. Authentication is the first layer of private application behavior.
MARKDOWN,
            'authorization-laravel-gates-policies-permissions' => <<<'MARKDOWN'
## Policy example

```php
public function update(User $user, Invoice $invoice): bool
{
    return $user->id === $invoice->owner_id || $user->role === 'admin';
}
```

This method answers a business question: may this user update this invoice? Policies make that rule easy to find and test.
MARKDOWN,
            'building-admin-dashboard-laravel' => <<<'MARKDOWN'
## Dashboard query example

```php
$stats = [
    'new_leads' => Lead::where('status', 'new')->count(),
    'overdue_tasks' => Task::wherePast('due_at')->whereNull('completed_at')->count(),
    'unpaid_invoices' => Invoice::where('status', 'unpaid')->count(),
];
```

Good dashboard numbers are tied to action. Each number should help the team decide what to do next.
MARKDOWN,
            'sending-emails-notifications-laravel' => <<<'MARKDOWN'
## Notification example

```php
$user->notify(new LeadAssignedNotification($lead));
```

That one line can send an email, store a database notification, or use another channel depending on how the notification is configured.
MARKDOWN,
            'laravel-apis-building-json-endpoints' => <<<'MARKDOWN'
## JSON endpoint example

```php
Route::get('/api/projects', function () {
    return ProjectResource::collection(
        Project::latest()->paginate(15)
    );
});
```

API resources help keep JSON responses consistent, which makes frontend and mobile work easier.
MARKDOWN,
            'laravel-testing-basics-real-projects' => <<<'MARKDOWN'
## Feature test example

```php
test('a guest can submit the contact form', function () {
    $this->post('/contact', [
        'name' => 'Youssef',
        'email' => 'client@example.com',
        'message' => 'I need a Laravel dashboard for my business.',
    ])->assertRedirect();
});
```

A test like this protects a workflow that can directly affect leads.
MARKDOWN,
            'laravel-queues-background-jobs-explained' => <<<'MARKDOWN'
## Dispatching a job

```php
GenerateMonthlyReport::dispatch($company);
```

The user can continue using the app while the report is created in the background. This is better than forcing a browser request to wait.
MARKDOWN,
            'laravel-security-best-practices-business-apps' => <<<'MARKDOWN'
## Authorization check example

```php
public function download(Invoice $invoice)
{
    $this->authorize('view', $invoice);

    return Storage::download($invoice->pdf_path);
}
```

The file download is protected by a policy check before Laravel returns the document.
MARKDOWN,
            'laravel-performance-optimization-speed-cache-queries' => <<<'MARKDOWN'
## Eager loading example

```php
$orders = Order::with(['customer', 'items.product'])
    ->latest()
    ->paginate(25);
```

Eager loading helps prevent repeated database queries while rendering a list. This is one of the first performance habits Laravel developers should learn.
MARKDOWN,
            'deploy-laravel-project-production' => <<<'MARKDOWN'
## Common deployment commands

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan queue:restart
```

The exact deployment process depends on the server, but production commands should be deliberate and repeatable.
MARKDOWN,
        ];

        return $snippets[$post['slug']] ?? '';
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function recap(array $post): string
    {
        $points = collect($post['sections'])
            ->take(4)
            ->map(fn (array $section): string => '- '.$section[0].' is part of understanding '.$post['search_intent'].' in real Laravel work.')
            ->implode("\n");

        return <<<MARKDOWN
In this lesson, you learned how {$post['search_intent']} fits into Laravel and why it matters beyond a simple tutorial example.

{$points}

The next step is to build a small example yourself. Keep it simple, name things clearly, and connect the code to a real workflow.
MARKDOWN;
    }

    private function navigation(?array $previous, ?array $next): string
    {
        $lines = ['## Continue the Laravel From Zero to Hero series'];

        if ($previous) {
            $lines[] = '- Previous lesson: /posts/'.$previous['slug'];
        }

        if ($next) {
            $lines[] = '- Next lesson: /posts/'.$next['slug'];
        }

        return implode("\n\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function expand(string $content, array $post): string
    {
        $additions = [
            <<<MARKDOWN
## Example project idea

To make {$post['title']} concrete, imagine a small internal tool for a service business. The owner wants to track requests, understand what needs attention, and avoid losing information in chat messages. You can practice {$post['search_intent']} inside that kind of project without needing a huge product idea.

Start with one user action related to {$post['search_intent']}. Maybe the user creates a lead, opens a dashboard, receives a notification, edits a record, or views a report. Then write the Laravel path for that action in the context of {$post['title']}. Which route starts it? Which controller handles it? Which model stores or retrieves data? Which Blade view or JSON response does the user see? Which validation or permission rule protects the action?

This {$post['title']} exercise turns the lesson into a small professional habit. Instead of learning Laravel as disconnected features, you learn {$post['search_intent']} as a chain of decisions. That is how real projects are built: one clear workflow, one responsible file at a time, with enough structure that the next feature does not create confusion.
MARKDOWN,
            <<<MARKDOWN
## How to practice this without getting stuck

The best way to practice {$post['search_intent']} is to build a small feature with a boring business goal. Do not start with a huge SaaS idea when working through {$post['title']}. Start with a contact request, a task list, a product catalog, a small blog, or a simple customer table. Small examples reveal the same Laravel patterns without burying you under too many decisions.

After the first {$post['search_intent']} example works, break it gently. Submit missing data. Use a wrong URL. Try to access something as a guest. Add one more field. Rename one variable. Move repeated code into a better place. This kind of practice teaches you how Laravel behaves when real users do imperfect things.

Keep notes while you work through {$post['title']}. Write down which file changed, why it changed, and what confused you. Those notes become your personal debugging guide. They also help when you move from tutorials to client projects, where the question is rarely "can you copy this code?" and more often "can you understand why this workflow is not working?"
MARKDOWN,
            <<<MARKDOWN
## How a senior developer would review this lesson

A senior Laravel developer reviewing {$post['title']} would not only ask whether the example runs. They would ask whether the responsibility is in the right place, whether names are clear, whether user input is protected, whether database queries will still behave with more data, and whether the next developer can safely change the feature.

For {$post['title']}, that review mindset matters. Beginners often measure progress by the number of concepts covered. Professionals measure progress by how confidently the application can change. A feature that works today but becomes confusing tomorrow is not finished in a professional sense.

When you review your own {$post['search_intent']} work, look for one improvement at a time. Maybe the route should use a controller. Maybe the controller should use a Form Request. Maybe the Blade view needs a component. Maybe the query should eager load a relationship. Maybe the action needs a policy. Small improvements create better instincts.
MARKDOWN,
            <<<MARKDOWN
## Mini assignment

Create a tiny Laravel feature connected to {$post['search_intent']}. Give it a real name, even if the project is only for practice. For example, use "leads", "projects", "invoices", "articles", "support requests", or "products" instead of empty demo names.

Your {$post['title']} assignment should include three parts. First, write what the user is trying to do. Second, write which Laravel files are involved. Third, write how you will know the feature works. If the answer is only "the page loads", make the check more specific. A better answer might be "a logged-in admin can create a lead, see it in the table, and receive a success message."

This habit makes {$post['search_intent']} tutorials useful. It trains you to think like someone building software for a real person, not only someone finishing a lesson.
MARKDOWN,
            <<<MARKDOWN
## Debugging notes for this topic

When something fails while learning {$post['search_intent']}, slow down and check the path of the request. Which URL was opened? Which route matched? Which controller or closure ran? Which model or database table was touched? Which view or JSON response came back? Most Laravel bugs become less scary when you follow that path one step at a time.

Read error messages carefully while practicing {$post['title']}. Laravel usually tells you whether the problem is a missing class, a wrong namespace, a database column that does not exist, a failed validation rule, a view that cannot be found, or a permission issue. Beginners often lose time because they search the web before reading the first useful line of the error for {$post['search_intent']}.

For {$post['title']}, create a small debugging checklist next to your practice code. Include the route name, the controller method, the model involved, the database table, and the expected result. If the feature breaks later, that checklist gives you a map back into the code.
MARKDOWN,
            <<<MARKDOWN
## What to write in your own notes

After finishing this lesson, write a short note in your own words. Start with: "{$post['search_intent']} helps me..." and complete the sentence with a real project example. Then write the files you touched and one mistake you want to avoid next time.

This sounds simple, but it is powerful for long-term learning. Laravel has many moving parts, and your memory improves when you connect each concept to a concrete feature. A developer who can explain the idea clearly is usually closer to using it well.

If you are building a portfolio, turn your {$post['title']} note into a project log. Explain what you built, what problem {$post['search_intent']} solves, and what you would improve in a second version. That kind of writing is also useful for personal branding because it shows how you think, not only which tools you have seen.
MARKDOWN,
            <<<MARKDOWN
## Readiness checklist before moving on

Before you continue to the next Laravel lesson, check whether you can use {$post['search_intent']} without copying the article word for word. You should be able to explain the concept, locate the relevant files, write a small example, and describe one real business situation where it matters.

If you cannot do that yet, repeat {$post['title']} with a smaller example. There is no shame in slowing down. Laravel becomes easier when the foundations are steady, especially with {$post['search_intent']}. Rushing through concepts creates the feeling of progress, but it often produces confusion when you try to build something original.

When you can explain {$post['title']} to another beginner, you are ready to connect it to the next topic in the series.
MARKDOWN,
        ];

        foreach ($additions as $addition) {
            if (self::wordCount($content) >= self::MIN_WORDS) {
                break;
            }

            $content .= "\n\n".$addition;
        }

        return $content;
    }
}
