<?php

namespace App\Services;

class ScheduledPostContent
{
    public function for(array $post): string
    {
        $sections = collect($post['sections'])
            ->map(fn (array $section, int $index): string => $this->section($post, $section, $index))
            ->implode("\n\n");

        $mistakes = $this->list($post['mistakes']);
        $checklist = $this->list($post['checklist']);
        $faq = $this->faq($post);
        $audienceHeading = $this->variant($post, [
            'Who this article is for',
            'Who should read this',
            'When this guide is useful',
            'The reader I am writing for',
        ]);
        $exampleHeading = $this->variant($post, [
            'Practical business example',
            'A realistic business situation',
            'What this looks like in a real company',
            'A grounded example',
        ]);
        $mistakesHeading = $this->variant($post, [
            'Common mistakes',
            'Mistakes that make this harder',
            'Where businesses usually lose time',
            'Avoid these planning traps',
        ]);
        $checklistHeading = $this->variant($post, [
            'Action checklist',
            'Before you approve the project',
            'A practical checklist',
            'What to prepare next',
        ]);
        $questionsHeading = $this->variant($post, [
            'Questions to ask before you build',
            'Questions that clarify the scope',
            'Questions worth answering early',
            'Planning questions that save rework',
        ]);
        $linksHeading = $this->variant($post, [
            'Internal links for deeper planning',
            'Related reading for the next decision',
            'Useful next reads',
            'Keep planning with these articles',
        ]);

        $content = <<<MARKDOWN
{$post['opening']}

{$post['context']}

## {$audienceHeading}

This is for {$post['audience']}. If you are comparing options around {$post['search_intent']}, the useful question is not only what can be built. The useful question is what should be built first, what should wait, and what would make the project easier to run after launch.

The technical side matters, but the business shape comes first. A good developer should be able to explain tradeoffs in normal language for {$post['category']}: the visible experience, the behind-the-scenes workflow, the data that needs care, and the maintenance work the business will inherit.

{$sections}

## {$exampleHeading}

{$post['example']}

The useful move is to turn this situation into a clear workflow. For {$post['title']}, that means writing the current steps, marking the parts that waste time or create doubt, and deciding which part should improve first. That usually produces a better first version than copying a competitor or asking for a long feature list without context.

## {$mistakesHeading}

{$mistakes}

These mistakes are rarely dramatic on day one. They become expensive later, when the team needs to update the system, explain {$post['search_intent']} to a new employee, run ads, add a new feature, or understand why leads are not converting. The best time to avoid them is before the project becomes too large to change comfortably.

## {$checklistHeading}

{$checklist}

Use this checklist before you request a quote, approve a proposal, or start development for {$post['title']}. If one item is unclear, that is not a failure. It is a sign that a short discovery conversation could save time and budget.

## Budget and scope notes

Budget depends on responsibility. In {$post['category']}, a public page has different risk than a client portal. A simple form has different risk than a payment flow. A dashboard that only reads data has different risk than an admin panel that changes orders, invoices, stock, permissions, or customer records.

The safest scope is usually the smallest version that a real person can use in a real workflow. For {$post['search_intent']}, that may mean fewer pages with stronger copy, one operational dashboard instead of ten charts, or a manual support process behind a clean user experience while the product proves demand.

Scope control is not about building less value. It is about protecting {$post['title']} from features that make the first launch slower without teaching the business anything useful.

## {$questionsHeading}

- What decision should this project make easier?
- What should a visitor, customer, staff member, or founder be able to do first?
- Which manual step is currently wasting the most time or causing the most mistakes?
- What information needs to be trusted?
- What should happen if a form, payment, import, email, or integration fails?
- Who will update content, review leads, manage records, or maintain the system after launch?
- What would make the first version successful enough to improve?

Those questions are simple, but they are powerful. They turn vague development requests about {$post['search_intent']} into practical project plans.

## {$linksHeading}

- /posts/what-to-prepare-before-hiring-web-developer
- /posts/difference-between-website-and-web-application
- /posts/choose-tech-stack-business-web-app

{$faq}

## Need help with this?

{$post['cta']}
MARKDOWN;

        return $this->expand($content, $post);
    }

    private function section(array $post, array $section, int $index): string
    {
        $points = $this->list($section['points']);
        $baseDetails = $post['details'][$index % count($post['details'])];
        $details = "{$baseDetails} In this article, that point applies specifically to {$section['heading']}, so the advice should be judged against the workflow and buyer expectation behind {$post['search_intent']}.";
        $pointsHeading = $this->variant($post, [
            'Practical points',
            'What to decide',
            'What to check',
            'How to make this useful',
        ], $index);

        return <<<MARKDOWN
## {$section['heading']}

{$section['body']}

{$details}

### {$pointsHeading}

{$points}
MARKDOWN;
    }

    private function faq(array $post): string
    {
        $items = collect($post['faq'])
            ->map(fn (array $item): string => "### {$item[0]}\n\n{$item[1]} For {$post['search_intent']}, the answer should always come back to the business workflow, the user journey, and the first decision the project needs to make easier.")
            ->implode("\n\n");

        $heading = $this->variant($post, [
            'FAQ',
            'Questions clients often ask',
            'Short answers before you start',
            'Practical FAQ',
        ]);

        return "## {$heading}\n\n{$items}";
    }

    private function list(array $items): string
    {
        return collect($items)
            ->map(fn (string $item): string => '- '.$item)
            ->implode("\n");
    }

    private function expand(string $content, array $post): string
    {
        if (ProductionPostContent::wordCount($content) >= ProductionPostContent::MIN_WORDS) {
            return $content;
        }

        $title = $post['title'];
        $audience = $post['audience'];
        $searchIntent = $post['search_intent'];
        $category = $post['category'];
        $approachHeading = $this->variant($post, [
            'How I would approach the first version',
            'How I would shape version one',
            'A sensible first build',
            'How I would reduce risk early',
        ]);
        $professionalHeading = $this->variant($post, [
            'What makes the project feel professional',
            'Where quality shows up',
            'What separates a serious build from a rushed one',
            'The details that make the project trustworthy',
        ]);
        $planningHeading = $this->variant($post, [
            "A practical planning method for {$audience}",
            "How {$audience} can prepare",
            "A planning method that fits {$searchIntent}",
            'A clear way to prepare the brief',
        ]);
        $developerHeading = $this->variant($post, [
            'What the developer should understand before writing code',
            'What should be clear before development starts',
            'Information your developer should not have to guess',
            'What to clarify before the build',
        ]);
        $measurementHeading = $this->variant($post, [
            'What to measure after launch',
            'How to judge the first version',
            'What useful feedback looks like',
            'How to review the project after launch',
        ]);
        $budgetHeading = $this->variant($post, [
            'Budget decisions that keep the project under control',
            'How to protect the budget without weakening the project',
            'Scope choices that prevent waste',
            'How to spend carefully',
        ]);
        $handoffHeading = $this->variant($post, [
            'Handoff notes that prevent confusion',
            'What to give your developer',
            'The handoff details that save time',
            'How to prepare project materials',
        ]);
        $reviewHeading = $this->variant($post, [
            'A simple review rhythm after launch',
            'How to keep improving after launch',
            'The first month after launch',
            'How to turn the launch into learning',
        ]);
        $content .= <<<MARKDOWN

## {$approachHeading}

For {$title}, I would start with a short discovery note rather than a design file. The note should describe the goal, the people involved, the current workflow, the data that matters, and the reason {$searchIntent} is worth doing now. This keeps the conversation honest. It also helps separate features that create value from features that only make the project feel larger.

The first version should include the pieces needed for real use: the main screen or page, the key form or workflow, the admin responsibility, the tracking or reporting, and the launch checks. For {$category}, it should avoid extra settings, secondary dashboards, and unusual edge cases unless those details are central to the business.

After launch, I would review what people actually did. Did visitors contact the business? Did staff use the dashboard? Did founders learn from the MVP? Did the automation save time? Did the form produce better leads? That feedback should guide the second version of {$title}.

## {$professionalHeading}

Professional does not mean complicated. For {$searchIntent}, it means the project is clear, fast, maintainable, and honest about its responsibilities. The copy should explain the offer. The interface should guide the next action. The backend should protect important data. The admin area should make routine work easier. The launch process should test forms, images, metadata, redirects, analytics, backups, and permissions.

Small details matter because they are the parts users touch. In {$title}, a confusing label creates support questions. A slow page loses trust. A missing email notification delays follow-up. A weak permission system creates risk. A vague service page makes the buyer unsure. A poor dashboard turns data into noise.

When those details are handled carefully, the project feels calm. People know what to do. The business can improve {$searchIntent} without starting over. That is the standard I would aim for before calling a web project ready.

## {$planningHeading}

Start by writing the business problem in one sentence. Not the feature request, not the preferred technology, and not the page count. Write the problem as the owner, founder, manager, or customer would experience it. A sentence like "we lose leads because nobody follows up quickly" is more useful than "we need a CRM." A sentence like "customers ask the same shipping questions before every purchase" is more useful than "we need a modern e-commerce website."

After that, list the people who will touch the system behind {$title}. A visitor may need confidence and a fast path to contact. A sales person may need lead notes, reminders, and status updates. An operations manager may need stock alerts, order visibility, or a report that can be trusted. A founder may need enough analytics to decide whether the product deserves another development cycle. Each person needs a slightly different version of clarity.

Then rank the work by risk. In {$category}, the risky part is often not the visual design. It is usually the workflow that controls data, payments, permissions, notifications, or customer communication. If that workflow is vague, the first version can look finished while still being hard to use. Good planning brings that risk into the open early, while changes are still cheap.

Finally, decide what should be deliberately left out. A serious project brief for {$title} should include a "not now" list. That list protects the launch. It tells everyone that a feature may be useful later, but does not belong in the first release. This is especially important for {$searchIntent}, because search results often make the topic sound simpler than it feels inside a real business.

## {$developerHeading}

A developer does not need every final detail before the first conversation, but they do need the business rules that cannot be guessed for {$searchIntent}. For example, if a dashboard shows revenue, should it count paid invoices only, confirmed orders, subscriptions, refunds, taxes, or manual adjustments? If a contact form qualifies leads, what makes a lead useful? If a user role can manage team members, who can remove access and who receives a notification?

These rules are not decorative. They are the difference between a system that matches the business and a system that only matches the first wireframe for {$title}. When rules are unclear, developers fill the gaps with assumptions. Some assumptions will be reasonable, but others will create extra work later. A short written explanation can prevent days of correction.

The developer should also understand how the project will be maintained. Who will edit text? Who will upload images? Who will receive form messages? Who will check failed payments, broken imports, or unread notifications? A feature that nobody owns becomes technical clutter. A simple feature with a clear owner is usually more valuable.

For international {$category} projects, communication rhythm matters too. Time zones, feedback windows, staging links, written decisions, and recorded walkthroughs keep the project moving without requiring everyone to be online at the same hour. Remote development works well when the project has a calm process and decisions are documented.

## {$measurementHeading}

Do not judge {$title} only by whether it looks finished. Judge it by whether it changes the business behavior it was built to improve. For a business website, useful measurements include qualified contact requests, form completion rate, page speed, search visibility, and which pages people visit before contacting you. For a dashboard, useful measurements include whether the team actually uses it, whether reports are trusted, and whether managers make decisions faster.

For a SaaS MVP connected to {$searchIntent}, the first measurements should be modest and honest. Are users reaching the main action? Do they understand the product without a long explanation? Where do they stop? Which support questions repeat? Which feature requests come from real use rather than imagination? Early data does not need to be perfect. It needs to point toward better decisions.

For automation, measure time and error reduction. If an invoice reminder, stock alert, lead notification, or report saves only a few minutes once, it may not matter. If it saves the team from repeating the same task every week, the value compounds. The best automation projects are usually boring in a good way: fewer missed follow-ups, fewer copied numbers, fewer manual exports, fewer status meetings built around finding information.

Keep the first reporting setup for {$title} simple. A small set of reliable numbers is better than a large analytics dashboard nobody trusts. Once the business knows which numbers matter, the system can grow around them.

## {$budgetHeading}

Budget problems often start when every idea for {$title} is treated as equally important. A better approach is to divide the scope into three groups: launch requirement, early improvement, and later idea. A launch requirement is needed for the project to work at all. An early improvement is useful but can wait until real users give feedback. A later idea is interesting, but not worth delaying the first release.

This simple split changes the conversation. Instead of asking "can we add this?", the team asks "does this belong before launch for {$searchIntent}?" That question protects the budget without blocking creativity. It also helps a developer propose cheaper paths, such as using a manual admin step behind the scenes while the customer-facing experience stays clean.

There is also a difference between cheap and controlled. Cheap often means important details are ignored. Controlled means the project is intentionally limited, documented, and built so the next version has room to grow. A controlled first version can be a strong business move. A cheap first version can become expensive when it has to be rebuilt.

If you are comparing proposals for {$title}, ask what each proposal includes after launch. Are basic fixes included? Is deployment included? Are backups, analytics, redirects, email testing, and security checks included? A lower number can hide missing work. A clearer proposal is often safer, even if it is not the cheapest.

## {$handoffHeading}

Before development starts on {$title}, create a handoff folder or document with the materials the developer will need. This may include brand assets, domain access, hosting details, current website links, example pages you like, product photos, existing spreadsheets, business rules, legal pages, payment account status, and the preferred contact email for notifications.

Write down the decisions that have already been made. If the business name, offer, target audience, pricing model, lead flow, or payment method is still uncertain, say that clearly. Uncertainty is not a problem when it is visible. It becomes a problem when it is hidden inside an approved scope.

The handoff for {$searchIntent} should also include examples of what should not happen. For instance, "customers should not see internal notes", "staff should not be able to export all records unless they are managers", "leads from paid ads should be tagged separately", or "old URLs should redirect to the new pages." Negative rules are often as important as positive requirements.

When the first build for {$title} is ready, test it with real tasks rather than only checking screens. Submit the form. Create a record. Change a status. Receive the email. Open the page on a phone. Check the social preview. Try a bad input. Log out and log back in. These small tests reveal whether the project is ready for real use.

## {$reviewHeading}

The first month after launching {$title} should not be silent. Set a weekly review for the numbers and behavior that matter. For a website, review search queries, contact form quality, page speed, mobile behavior, and pages that need clearer copy. For a dashboard or CRM, review whether users are updating records, whether reminders are useful, and whether reports match the real business.

Keep a small improvement log for {$searchIntent}. Each item should include the problem, the evidence, and the possible fix. "The contact page is weak" is vague. "Visitors from service pages open the contact page but do not submit the form" is something a developer and copywriter can work with. "The team is not using the dashboard" is vague. "The dashboard does not show overdue tasks, so managers still ask in chat" is useful.

This review rhythm turns {$title} into an asset instead of a one-time expense. The best web systems improve because the owner keeps learning from real use. That does not mean constant redesign. It means careful, measured improvement: clearer copy, better forms, faster pages, stronger reports, safer permissions, and workflows that match how the business actually operates.

If the first version of {$title} was built with clean structure, these improvements are easier. If it was rushed without planning, every improvement can feel like a fight with the old code. That is why planning, scope control, and honest maintenance expectations matter from the beginning.
MARKDOWN;

        return $content;
    }

    private function variant(array $post, array $options, int $offset = 0): string
    {
        return $options[(abs(crc32($post['slug'])) + $offset) % count($options)];
    }
}
