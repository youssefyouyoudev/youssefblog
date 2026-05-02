<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EditorialCleanupPosts extends Command
{
    protected $signature = 'blog:editorial-cleanup
        {--dry-run : Show what would change without saving}
        {--status= : Limit cleanup to one status}
        {--limit= : Clean only this many changed posts}
        {--only= : Clean one specific post by slug}
        {--draft-below=0 : Move posts below this editorial score to draft. Use 0 to disable}';

    protected $description = 'Remove AI-template patterns, placeholders, duplicate blocks, and weak metadata from existing posts.';

    private const FORBIDDEN_PHRASES = [
        'In today\'s digital world',
        'This guide will help you navigate',
        'Whether you are a beginner or professional',
        'It is important to understand',
        'Let\'s dive in',
        'Unlock the power of',
        'Game-changer',
        'Revolutionary',
        'Comprehensive guide',
        'In conclusion',
        'This topic matters because',
        'Use this workflow before you build',
    ];

    private const EDITORIAL_NOTES = [
        'links should be reviewed',
        'if related posts are scheduled',
        'editor note',
        'note to self',
        'internal link placeholder',
        'ready to publish',
        'seeded as draft',
        'generated article',
        'lorem ipsum',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $only = $this->option('only') ? Str::slug((string) $this->option('only')) : null;
        $limit = $this->option('limit') ? max(1, (int) $this->option('limit')) : null;
        $draftBelow = max(0, (float) $this->option('draft-below'));

        $posts = Post::with(['category', 'tags'])
            ->when($this->option('status'), fn ($query, string $status) => $query->where('status', $status))
            ->when($only, fn ($query) => $query->where('slug', $only))
            ->orderBy('id')
            ->get();

        if ($only && $posts->isEmpty()) {
            $this->error("No post found for slug [{$only}].");

            return self::FAILURE;
        }

        if (! $dryRun) {
            $this->backupPosts($posts);
        }

        $rows = [];
        $changed = 0;
        $skipped = 0;

        foreach ($posts as $post) {
            $oldScore = $this->editorialScore($post);
            $payload = $this->cleanPayload($post, $draftBelow);
            $changes = $this->changedFields($post, $payload);

            if ($changes === []) {
                $skipped++;
                $rows[] = $this->row($post, 'skipped', [], $oldScore, $oldScore);
                continue;
            }

            if ($limit && $changed >= $limit) {
                $skipped++;
                $rows[] = $this->row($post, 'skipped by limit', $changes, $oldScore, $oldScore);
                continue;
            }

            if (! $dryRun) {
                $post->fill($payload)->save();
            }

            $changed++;
            $preview = clone $post;
            $preview->fill($payload);
            $newScore = $this->editorialScore($preview);
            $rows[] = $this->row($post, $dryRun ? 'would clean' : 'cleaned', $changes, $oldScore, $newScore);
        }

        $this->writeReport($rows, $posts->count(), $changed, $skipped, $dryRun);

        $mode = $dryRun ? 'Editorial dry run complete' : 'Editorial cleanup complete';
        $this->info("{$mode}: {$changed} changed, {$skipped} skipped.");
        $this->line('Report: storage/app/reports/editorial-cleanup-report.md');

        return self::SUCCESS;
    }

    private function cleanPayload(Post $post, float $draftBelow): array
    {
        $content = $this->cleanContent((string) $post->content, (string) $post->title);
        $score = $this->editorialScore($post);
        $cluster = $this->cluster($post);
        $excerpt = $this->excerptFrom($content, $cluster);
        $metaDescription = $this->metaDescriptionFrom($content, $post, $cluster);
        $metaTitle = $this->metaTitle($post->title);

        $payload = [
            'content' => $content,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'seo_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => $this->keywords($post, $cluster),
            'reading_time' => max(1, (int) ceil(Str::wordCount(strip_tags($content)) / 220)),
            'last_updated_at' => now(),
            'schema_type' => 'BlogPosting',
        ];

        if (filled($post->featured_image) && blank($post->featured_image_alt)) {
            $payload['featured_image_alt'] = $this->imageAlt($post, $cluster);
        }

        if ($draftBelow > 0 && $score < $draftBelow && Schema::hasColumn('posts', 'status')) {
            $payload['status'] = 'draft';
        }

        return array_intersect_key($payload, array_flip(Schema::getColumnListing((new Post)->getTable())));
    }

    private function cleanContent(string $content, string $title): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = $this->fixEncodingArtifacts($content);
        $content = $this->removeForbiddenSentences($content, $title);
        $content = $this->removeEditorialNoteLines($content);
        $content = $this->normalizeHeadings($content);
        $content = $this->removeDuplicateFaqSections($content);
        $content = $this->removeDuplicateCta($content);

        return Str::of($content)
            ->replaceMatches("/\n{3,}/", "\n\n")
            ->trim()
            ->toString();
    }

    private function fixEncodingArtifacts(string $content): string
    {
        return str_replace([
            'â€œ',
            'â€',
            'â€™',
            'â€˜',
            'â€”',
            'â€“',
            'â€¦',
        ], [
            '"',
            '"',
            "'",
            "'",
            '-',
            '-',
            '...',
        ], $content);
    }

    private function removeForbiddenSentences(string $content, string $title): string
    {
        foreach (self::FORBIDDEN_PHRASES as $phrase) {
            $pattern = '/(^|[\n.!?]\s+)[^\n.!?]*'.preg_quote($phrase, '/').'[^\n.!?]*[.!?]?/iu';
            $content = preg_replace($pattern, '$1', $content) ?? $content;
        }

        $titlePattern = preg_quote($title, '/');
        $content = preg_replace('/^\s*'.$titlePattern.'\s+matters because[^\n.]*[.]?\s*$/ium', '', $content) ?? $content;
        $content = preg_replace('/^\s*Use this workflow before you build, buy, automate, or publish anything related to\s+'.$titlePattern.'[.]?\s*$/ium', '', $content) ?? $content;

        return $content;
    }

    private function removeEditorialNoteLines(string $content): string
    {
        $lines = collect(explode("\n", $content))
            ->reject(function (string $line): bool {
                $lower = Str::lower(trim($line));

                if ($lower === '') {
                    return false;
                }

                return collect(self::EDITORIAL_NOTES)
                    ->contains(fn (string $phrase): bool => Str::contains($lower, $phrase));
            })
            ->values();

        return $lines->implode("\n");
    }

    private function normalizeHeadings(string $content): string
    {
        $replacements = [
            '## Beginner FAQ' => '## FAQ',
            '## Frequently Asked Questions' => '## FAQ',
            '## Final Summary' => '## What to do next',
            '## Final Thoughts' => '## What to do next',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    private function removeDuplicateFaqSections(string $content): string
    {
        $matches = [];
        preg_match_all('/^##\s+FAQ\s*$/im', $content, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches[0]) <= 1) {
            return $content;
        }

        $firstOffset = $matches[0][0][1];
        $parts = [];
        $cursor = 0;

        foreach (array_slice($matches[0], 1) as $match) {
            $offset = $match[1];
            $parts[] = substr($content, $cursor, $offset - $cursor);
            $next = preg_match('/^##\s+/m', $content, $nextMatch, PREG_OFFSET_CAPTURE, $offset + 1)
                ? $nextMatch[0][1]
                : strlen($content);
            $cursor = $next;
        }

        $parts[] = substr($content, $cursor);
        $content = implode('', $parts);

        return substr($content, 0, $firstOffset).substr($content, $firstOffset);
    }

    private function removeDuplicateCta(string $content): string
    {
        $needle = 'Need help building a Laravel, SaaS, or business website? Work with Youssef Youyou: https://youssefyouyou.com';
        $first = strpos($content, $needle);

        if ($first === false) {
            return $content;
        }

        $before = substr($content, 0, $first + strlen($needle));
        $after = str_replace($needle, '', substr($content, $first + strlen($needle)));

        return $before.$after;
    }

    private function excerptFrom(string $content, string $cluster): string
    {
        $plain = Str::of(strip_tags($content))->replaceMatches('/[#*_>`-]+/', ' ')->squish()->toString();
        $sentence = Str::of($plain)->before('.')->squish()->toString();

        if (Str::length($sentence) < 90) {
            $sentence = match ($cluster) {
                'Laravel' => 'A practical Laravel guide with clear examples, common mistakes, and production-friendly advice for beginners.',
                'Finance' => 'A realistic finance guide for freelancers with simple examples, risk warnings, and practical next steps.',
                'AI Tools' => 'A practical AI guide focused on useful workflows, human review, privacy, and avoiding low-quality automation.',
                'SaaS' => 'A practical SaaS guide for validating ideas, keeping scope small, and building useful software before adding complexity.',
                default => 'A practical guide with clear steps, realistic examples, common mistakes, and useful next actions.',
            };
        }

        return Str::limit($sentence, 150, '');
    }

    private function metaDescriptionFrom(string $content, Post $post, string $cluster): string
    {
        $keyword = Str::of($post->title)->replaceMatches('/[^A-Za-z0-9 ]/', ' ')->squish()->lower()->words(7, '')->toString();
        $base = match ($cluster) {
            'Laravel' => "Learn {$keyword} with Laravel examples, file paths, common mistakes, and practical checks you can use on real projects.",
            'Finance' => "Learn {$keyword} with realistic examples, freelancer context, risk warnings, and practical steps without fake promises.",
            'AI Tools' => "Learn {$keyword} with practical workflows, tool limits, human review advice, and examples for developers and freelancers.",
            'SaaS' => "Learn {$keyword} with validation steps, MVP scope, pricing context, mistakes to avoid, and practical SaaS examples.",
            default => "Learn {$keyword} with practical examples, common mistakes, checklists, and clear next steps for real projects.",
        };

        return Str::limit(Str::of($base)->squish()->toString(), 158, '');
    }

    private function metaTitle(string $title): string
    {
        $title = trim($title);

        if (Str::length($title) <= 58) {
            return $title;
        }

        return preg_replace('/\s+\S*$/', '', Str::limit($title, 58, '')) ?: Str::limit($title, 58, '');
    }

    private function keywords(Post $post, string $cluster): array
    {
        $existing = collect($post->keywords ?? [])->filter()->values();
        $base = Str::of($post->title)->replaceMatches('/[^A-Za-z0-9 ]/', ' ')->squish()->lower()->words(7, '')->toString();

        return $existing
            ->merge([$base, Str::lower($cluster), $post->category?->slug])
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    private function imageAlt(Post $post, string $cluster): string
    {
        return match ($cluster) {
            'Laravel' => 'Laravel project workspace for '.$post->title,
            'Finance' => 'Freelancer finance planning notes for '.$post->title,
            'AI Tools' => 'AI workflow planning screen for '.$post->title,
            'SaaS' => 'SaaS product planning workspace for '.$post->title,
            default => 'Practical guide workspace for '.$post->title,
        };
    }

    private function cluster(Post $post): string
    {
        $haystack = Str::lower($post->title.' '.$post->category?->name.' '.$post->slug.' '.collect($post->keywords ?? [])->implode(' '));

        return match (true) {
            Str::contains($haystack, ['laravel', 'blade', 'controller', 'eloquent', 'migration', 'php', 'vite']) => 'Laravel',
            Str::contains($haystack, ['finance', 'money', 'budget', 'invest', 'saving', 'cash']) => 'Finance',
            Str::contains($haystack, ['ai', 'chatgpt', 'prompt', 'automation']) => 'AI Tools',
            Str::contains($haystack, ['saas', 'mvp', 'subscription', 'crm']) => 'SaaS',
            default => $post->category?->name ?: 'Business',
        };
    }

    private function editorialScore(Post $post): float
    {
        $content = (string) $post->content;
        $plain = Str::of(strip_tags($content))->squish()->toString();
        $score = 10.0;

        if (Str::wordCount($plain) < 800) {
            $score -= 2.5;
        } elseif (Str::wordCount($plain) < 1200) {
            $score -= 1.5;
        }

        if (substr_count($content, '## ') < 4) {
            $score -= 1.0;
        }

        foreach ([...self::FORBIDDEN_PHRASES, ...self::EDITORIAL_NOTES] as $phrase) {
            if (Str::contains(Str::lower($content), Str::lower($phrase))) {
                $score -= 0.8;
            }
        }

        if (substr_count(Str::lower($content), '## faq') > 1) {
            $score -= 1.0;
        }

        return max(1, round($score, 1));
    }

    private function changedFields(Post $post, array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $key): bool => $post->getAttribute($key) != $value)
            ->keys()
            ->values()
            ->all();
    }

    private function backupPosts(Collection $posts): void
    {
        File::ensureDirectoryExists(storage_path('app/backups'));

        $filename = 'posts-before-editorial-cleanup-'.now('Africa/Casablanca')->format('Y-m-d-His').'.json';
        $data = $posts->map(fn (Post $post): array => [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
            'published_at' => $post->published_at?->toDateTimeString(),
            'excerpt' => $post->getRawOriginal('excerpt'),
            'content' => $post->content,
            'meta_title' => $post->meta_title,
            'seo_title' => $post->seo_title,
            'meta_description' => $post->meta_description,
            'keywords' => $post->keywords,
            'faqs' => $post->faqs,
        ])->values()->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        File::put(storage_path('app/backups/'.$filename), $data);
    }

    private function row(Post $post, string $status, array $changes, float $oldScore, float $newScore): array
    {
        return [
            'score' => "{$oldScore} -> {$newScore}",
            'status' => $status,
            'post_status' => $post->status,
            'slug' => $post->slug,
            'title' => $post->title,
            'changes' => $changes,
        ];
    }

    private function writeReport(array $rows, int $scanned, int $changed, int $skipped, bool $dryRun): void
    {
        File::ensureDirectoryExists(storage_path('app/reports'));

        $report = "# Editorial Cleanup Report\n\n";
        $report .= 'Generated at: '.now('Africa/Casablanca')->toDateTimeString()."\n";
        $report .= 'Mode: '.($dryRun ? 'dry run' : 'saved changes')."\n";
        $report .= "Posts scanned: {$scanned}\n";
        $report .= "Posts changed: {$changed}\n";
        $report .= "Posts skipped: {$skipped}\n\n";
        $report .= "## Changed Posts\n\n";

        foreach ($rows as $row) {
            if ($row['status'] === 'skipped') {
                continue;
            }

            $report .= "- {$row['score']} | {$row['status']} | {$row['post_status']} | {$row['slug']} | {$row['title']}";
            $report .= $row['changes'] ? ' | fields: '.implode(', ', $row['changes']) : ' | fields: none';
            $report .= "\n";
        }

        $report .= "\n## Cleanup Rules\n\n";
        $report .= "- Removed forbidden AI-template phrases, visible editorial notes, placeholders, duplicate FAQ headings, duplicate final CTAs, and encoding artifacts.\n";
        $report .= "- Kept slugs stable. No deletes. Drafting happens only when --draft-below is explicitly greater than 0.\n";

        File::put(storage_path('app/reports/editorial-cleanup-report.md'), $report);
    }
}
