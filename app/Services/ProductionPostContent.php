<?php

namespace App\Services;

use Illuminate\Support\Str;

class ProductionPostContent
{
    public const MIN_WORDS = 3000;

    public static function bannedPhrases(): array
    {
        return [
            'in today\'s digital landscape',
            'unlock the power',
            'game-changer',
            'revolutionize',
            'seamless',
            'robust solution',
            'cutting-edge',
            'leverage',
            'next level',
            'supercharge',
            'empower your business',
            'transform your business overnight',
            'skyrocket',
            'dominate Google',
            'guaranteed results',
            'world-class',
            'whether you are a beginner or an expert',
            'dive into',
            'let\'s explore',
            'at the end of the day',
            'look no further',
            'comprehensive guide repeated everywhere',
        ];
    }

    public static function wordCount(string $content): int
    {
        return Str::wordCount(strip_tags($content));
    }

    public function for(array $post): string
    {
        $guide = $this->guide($post['slug']);
        $content = collect([
            $this->opening($post, $guide),
            $this->readerPromise($post, $guide),
            $this->sections($post, $guide),
            $this->comparisonTable($guide),
            $this->scenario($guide),
            $this->scopeNotes($post, $guide),
            $this->mistakes($guide),
            $this->checklist($guide),
            $this->decisionFramework($post, $guide),
            $this->relatedLinks($post),
            $this->cta($guide),
        ])->filter()->implode("\n\n");

        return $this->expandUntilPremium($content, $post, $guide);
    }

    private function guide(string $slug): array
    {
        $guides = require database_path('seeders/data/production-post-content.php');

        return $guides[$slug] ?? [];
    }

    private function opening(array $post, array $guide): string
    {
        return <<<MARKDOWN
{$guide['opening']}

{$guide['reader_context']}

This article is written for business owners, founders, operators, and decision-makers who need a practical way to think about {$guide['topic_phrase']}. You do not need to know every technical detail before speaking with a developer. You do need to understand the business tradeoffs, the risks that usually appear later, and the questions that make a project easier to estimate, build, launch, and maintain.

The goal is not to make the decision sound more complicated than it is. The goal is to make the hidden parts visible before money and time are already committed. A good web project has design, engineering, content, operations, and maintenance working together. When one of those parts is ignored, the project may still launch, but it becomes harder to trust and harder to improve.
MARKDOWN;
    }

    private function readerPromise(array $post, array $guide): string
    {
        return <<<MARKDOWN
## What this guide will help you decide

By the end, you should be able to explain what matters, what can wait, and what should be clarified before a quote or build starts. You should also be able to spot the difference between a surface-level answer and a serious project plan.

For {$guide['audience']}, the important question is not only "can this be built?" Most things can be built with enough time and budget. The better question is: "what is the smallest professional version that solves the real business problem without creating avoidable maintenance trouble?"

Here is the lens I use when advising clients:

- What is the business outcome?
- Who will use the system or website every week?
- What information needs to be trusted?
- What should happen when something fails?
- What will need to change after launch?
- Who will maintain the project when the first version is no longer enough?

Those questions sound simple, but they prevent many expensive mistakes. They also help non-technical teams speak clearly with developers, designers, marketers, and internal staff.
MARKDOWN;
    }

    private function sections(array $post, array $guide): string
    {
        return collect($guide['sections'])
            ->map(fn (array $section): string => $this->section($section, $guide))
            ->implode("\n\n");
    }

    private function section(array $section, array $guide): string
    {
        $points = collect($section['points'])
            ->map(fn (string $point): string => '- '.$point)
            ->implode("\n");

        $questions = collect($section['questions'])
            ->map(fn (string $question): string => '- '.$question)
            ->implode("\n");

        $headingLower = Str::lower($section['heading']);

        return <<<MARKDOWN
## {$section['heading']}

{$section['body']}

This part deserves careful thinking because {$headingLower} usually touches more than one team. It can affect sales conversations, admin work, content updates, reporting, customer support, and future development. If it is treated as a small detail, the project may still launch, but the weak spot returns later as confusion, rework, or avoidable support requests.

When planning {$guide['topic_phrase']}, I would connect {$headingLower} to a real workflow instead of discussing it as an abstract feature. Who notices the problem first? What should the system show them? What should happen automatically? What should stay under human review? Clear answers keep the work practical and prevent this part of the build from becoming a disconnected idea.

### What to inspect

{$points}

### Questions worth asking

{$questions}

A useful answer about {$headingLower} should be specific enough to change the project plan. If the answer only sounds positive but does not affect scope, budget, timeline, or responsibility, it is probably not detailed enough yet. Good planning is not about making the project heavy. It is about removing uncertainty before uncertainty becomes rework.
MARKDOWN;
    }

    private function comparisonTable(array $guide): string
    {
        $rows = collect($guide['decision_table'])
            ->map(fn (array $row): string => '| '.$row[0].' | '.$row[1].' | '.$row[2].' |')
            ->implode("\n");

        return <<<MARKDOWN
## Decision table

Use this table as a quick filter before choosing a direction. It is not a replacement for discovery, but it helps you avoid comparing options that solve different problems.

| Situation | Better first move | Why it matters |
|---|---|---|
{$rows}

The table is intentionally practical. Most businesses do not need a perfect technical answer on day one. They need a decision that protects the project from obvious risk, keeps the first version understandable, and leaves room to improve after real usage.
MARKDOWN;
    }

    private function scenario(array $guide): string
    {
        return <<<MARKDOWN
## Realistic business scenario

{$guide['scenario']}

This is the kind of situation where the conversation should slow down for a moment. Not because the project is impossible, but because the first plan is usually too vague. A serious developer should ask about roles, content, user journeys, data, reporting, integrations, and launch responsibilities before promising a price or timeline.

The practical version one should focus on the part of the workflow that creates the most value. If the business needs more leads, the first build should improve trust, contact flow, tracking, and follow-up. If the business needs fewer manual tasks, the first build should improve data entry, reminders, exports, dashboards, and status visibility. If the business needs a SaaS MVP, the first build should prove the paid workflow before adding secondary features.

That is how a project becomes useful instead of just finished.
MARKDOWN;
    }

    private function scopeNotes(array $post, array $guide): string
    {
        return <<<MARKDOWN
## Budget, scope, and maintenance notes

Budget should be connected to responsibility. A small page update, a professional business website, a custom Laravel dashboard, and a SaaS MVP do not carry the same responsibility. The more the project stores important data, affects revenue, or becomes part of operations, the more attention it needs around validation, security, backups, permissions, and support.

Here are the scope layers I would discuss before estimating {$guide['topic_phrase']}:

- Strategy: the business goal, target user, buyer questions, and success criteria.
- Content: page copy, service details, product data, screenshots, testimonials, FAQs, and legal pages where needed.
- Interface: layout, mobile behavior, forms, dashboard screens, tables, filters, and admin controls.
- Data: records, relationships, required fields, imports, exports, and retention needs.
- Integrations: payments, email, CRM, analytics, APIs, calendars, inventory, shipping, AI tools, or third-party services.
- Operations: who updates the system, who reviews errors, who receives notifications, and how support requests are handled.
- Launch: redirects, analytics checks, form tests, backups, environment setup, and post-launch review.

The first professional version does not need every possible feature. It needs enough structure that the next version is not painful. This is where experienced planning saves money. A rushed build often feels cheaper because it skips decisions. Those decisions still return later, usually when changing them is harder.

For {$guide['audience']}, I recommend writing three lists before starting: must-have, should-have, and later. Must-have features are required for the project to create value. Should-have features improve the experience but can wait if budget is tight. Later features belong to the roadmap and should not slow down launch unless they change the core architecture.
MARKDOWN;
    }

    private function mistakes(array $guide): string
    {
        $items = collect($guide['mistakes'])
            ->map(fn (string $mistake): string => '- '.$mistake)
            ->implode("\n");

        return <<<MARKDOWN
## Common mistakes to avoid

{$items}

These mistakes are common because they feel reasonable during a busy project. A founder wants to add one more feature. A business owner wants to save money on planning. A team wants to copy a competitor. A marketer wants to run ads before the landing page is ready. None of those decisions are automatically wrong, but each one needs context.

The safer habit is to ask what the decision does to maintenance. Does it create another account to manage? Another integration that can fail? Another page that needs content? Another permission level? Another report that nobody checks? Another promise the team must keep? If the answer is yes, the feature may still be worth building, but it should be treated as a real responsibility.
MARKDOWN;
    }

    private function checklist(array $guide): string
    {
        $items = collect($guide['checklist'])
            ->map(fn (string $item): string => '- '.$item)
            ->implode("\n");

        return <<<MARKDOWN
## Action checklist

{$items}

Do not treat this checklist as paperwork. Treat it as a way to protect the project. If a point is unclear, that is useful information. It means the project needs a conversation, a smaller first version, or a better definition of done.
MARKDOWN;
    }

    private function decisionFramework(array $post, array $guide): string
    {
        return <<<MARKDOWN
## A simple decision framework

Before you hire, redesign, automate, rebuild, or choose a stack, score the project on four areas: business value, user clarity, technical risk, and maintenance effort.

Business value asks whether the project is tied to revenue, time saved, trust, customer experience, or operational control. User clarity asks whether you know who will use it and what they need to accomplish first. Technical risk asks whether the project depends on complex data, third-party services, custom logic, payments, privacy, or high traffic. Maintenance effort asks who will update it, monitor it, and improve it after launch.

If business value is high and user clarity is low, start with discovery and prototypes. If user clarity is high and technical risk is low, move toward a focused build. If technical risk is high, pay attention to architecture, tests, logs, backups, and deployment. If maintenance effort is high, simplify the feature list or invest in admin tools and documentation.

For {$post['title']}, this framework keeps the project grounded. It helps you avoid buying a shiny feature when the real need is better content, cleaner data, faster pages, safer permissions, or a dashboard that the owner can actually read every Friday.
MARKDOWN;
    }

    private function relatedLinks(array $post): string
    {
        $links = collect($post['related'])
            ->map(fn (string $slug): string => '- /posts/'.$slug)
            ->implode("\n");

        return <<<MARKDOWN
## Related reading

These related articles can help you connect this decision to the wider project:

{$links}

You can also visit my portfolio at https://youssefyouyou.com to see how I position practical web development, Laravel systems, SaaS MVPs, dashboards, automation, and business websites.
MARKDOWN;
    }

    private function cta(array $guide): string
    {
        return <<<MARKDOWN
## Need help planning this properly?

{$guide['cta']}
MARKDOWN;
    }

    private function expandUntilPremium(string $content, array $post, array $guide): string
    {
        if (self::wordCount($content) >= self::MIN_WORDS) {
            return $content;
        }

        $additions = [
            <<<MARKDOWN
## How I would approach this as a developer

If a client came to me with this problem, I would not start by choosing colors, packages, or hosting. I would start by writing the workflow in plain English. Who arrives first? What do they need? What information do they provide? Who reviews it? What should happen automatically? What should stay manual? What should be visible to the owner? What should be hidden from normal users?

That plain-English workflow becomes the foundation for the database, screens, forms, notifications, permissions, and launch plan. It also reveals where the project can be simplified. Many projects become cheaper and better when the first version focuses on the work people actually repeat every week.

For {$guide['topic_phrase']}, I would also define the first useful review date. A website might be reviewed after the first thirty inquiries. A dashboard might be reviewed after the first month of reports. A CRM might be reviewed after the first two weeks of follow-ups. A SaaS MVP might be reviewed after the first real users complete the main workflow. Without a review point, the project can drift into opinions instead of evidence.
MARKDOWN,
            <<<'MARKDOWN'
## What non-technical teams should document

You do not need to write technical specifications like an engineer. You can still prepare useful material. Write the business goal, the current pain, the people involved, the existing tools, the content you already have, and the decisions you are unsure about.

For example, if the project involves customers, list the customer stages. If it involves products, list the product attributes. If it involves invoices, list the invoice statuses. If it involves reports, list the questions the report should answer. If it involves permissions, list what each role should be allowed to see or change.

This kind of preparation helps a developer estimate honestly. It also protects you from vague proposals. A proposal should respond to your actual workflow, not just list technologies and a final price.
MARKDOWN,
        ];

        foreach ($additions as $addition) {
            $content .= "\n\n".$addition;

            if (self::wordCount($content) >= self::MIN_WORDS) {
                break;
            }
        }

        return $content;
    }
}
