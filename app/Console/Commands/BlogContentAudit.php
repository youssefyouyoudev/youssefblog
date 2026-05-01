<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BlogContentAudit extends Command
{
    protected $signature = 'blog:content-audit {--status= : Limit to one post status}';

    protected $description = 'Score every blog post for SEO, depth, helpfulness, and AI-template risk.';

    public function handle(): int
    {
        $posts = Post::with(['category', 'tags'])
            ->when($this->option('status'), fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('published_at')
            ->orderBy('id')
            ->get();

        $rows = $posts->map(fn (Post $post): array => $this->scorePost($post));
        $groups = [
            'Strong posts to improve slightly' => $rows->filter(fn ($row) => $row['score'] >= 8),
            'Medium posts to expand' => $rows->filter(fn ($row) => $row['score'] >= 6 && $row['score'] < 8),
            'Weak posts that need major rewrite' => $rows->filter(fn ($row) => $row['score'] >= 4 && $row['score'] < 6),
            'Posts that should be hidden/drafted until improved' => $rows->filter(fn ($row) => $row['score'] < 4),
        ];

        $report = "# Blog Content Audit\n\n";
        $report .= 'Generated at: '.now('Africa/Casablanca')->toDateTimeString()."\n";
        $report .= "Posts scanned: {$rows->count()}\n";
        $report .= 'Average score: '.number_format($rows->avg('score') ?: 0, 1)."/10\n\n";
        $report .= "## Scoring Rules\n\n";
        $report .= "Scores consider title, slug, metadata, intro, search intent, depth, human feel, examples, internal links, FAQ, conclusion, category fit, and AI-template risk.\n\n";

        foreach ($groups as $label => $groupRows) {
            $report .= "## {$label} ({$groupRows->count()})\n\n";

            if ($groupRows->isEmpty()) {
                $report .= "- None\n\n";
                continue;
            }

            foreach ($groupRows->sortBy('score') as $row) {
                $report .= "- {$row['score']}/10 | {$row['status']} | {$row['words']} words | {$row['category']} | {$row['slug']} | {$row['title']}\n";
                $report .= '  - Issues: '.($row['issues'] ? implode('; ', $row['issues']) : 'minor polish only')."\n";
                $report .= '  - Strengths: '.($row['strengths'] ? implode('; ', $row['strengths']) : 'none detected')."\n";
            }

            $report .= "\n";
        }

        $report .= "## Full Post Ratings\n\n";
        foreach ($rows as $row) {
            $report .= "| {$row['score']} | {$row['status']} | {$row['words']} | {$row['category']} | {$row['slug']} | {$row['title']} |\n";
        }

        File::ensureDirectoryExists(storage_path('app/reports'));
        File::put(storage_path('app/reports/blog-content-audit.md'), $report);

        $this->info("Content audit complete: {$rows->count()} posts scanned.");
        $this->line('Report: storage/app/reports/blog-content-audit.md');

        return self::SUCCESS;
    }

    private function scorePost(Post $post): array
    {
        $content = (string) $post->content;
        $plain = Str::of(strip_tags($content))->squish()->toString();
        $lower = Str::lower($content);
        $words = Str::wordCount($plain);
        $issues = [];
        $strengths = [];
        $score = 10.0;

        if ($words < 800) {
            $score -= 2.5;
            $issues[] = 'thin content under 800 words';
        } elseif ($words < 1200) {
            $score -= 1.8;
            $issues[] = 'short support article';
        } elseif ($words < 1800) {
            $score -= 0.8;
            $issues[] = 'could use more practical depth';
        } else {
            $strengths[] = 'solid word count';
        }

        if (substr_count($content, '## ') < 5) {
            $score -= 1.2;
            $issues[] = 'weak heading structure';
        } else {
            $strengths[] = 'clear headings';
        }

        if (! filled($post->meta_title ?: $post->seo_title)) {
            $score -= 0.7;
            $issues[] = 'missing SEO title';
        }

        $metaLength = mb_strlen((string) $post->meta_description);
        if ($metaLength < 120 || $metaLength > 165) {
            $score -= 0.8;
            $issues[] = 'meta description length is weak';
        } else {
            $strengths[] = 'usable meta description';
        }

        if (! preg_match('/\]\(\/posts\/[a-z0-9-]+\)|\/posts\/[a-z0-9-]+/', $content)) {
            $score -= 0.8;
            $issues[] = 'missing body internal links';
        }

        if (! Str::contains($lower, 'faq')) {
            $score -= 0.7;
            $issues[] = 'missing FAQ';
        } elseif (count($post->faqs ?? []) >= 4) {
            $strengths[] = 'schema-friendly FAQ data';
        }

        if (! Str::contains($lower, ['checklist', 'common mistakes', 'step-by-step', 'practical example'])) {
            $score -= 1.1;
            $issues[] = 'missing practical example/checklist layer';
        } else {
            $strengths[] = 'practical structure';
        }

        if (preg_match('/in today.s digital world|game changer|unlock your potential|delve into|comprehensive guide|leverage|seamlessly|it is important to note/i', $content)) {
            $score -= 1.0;
            $issues[] = 'AI-marketing phrase risk';
        }

        if (Str::contains($lower, ['guaranteed ranking', 'guaranteed income', 'get rich quick', 'first million'])) {
            $score -= 1.0;
            $issues[] = 'trust-risk language';
        }

        if (Str::length($post->slug) > 80) {
            $score -= 0.4;
            $issues[] = 'slug is long';
        }

        $intro = Str::of($plain)->limit(450, '')->toString();
        if (! Str::contains(Str::lower($intro), Str::lower(Str::before($post->title, ':')))) {
            $score -= 0.3;
            $issues[] = 'intro may not answer intent fast';
        }

        return [
            'id' => $post->id,
            'score' => max(1, min(10, round($score, 1))),
            'status' => $post->status,
            'words' => $words,
            'category' => $post->category?->name ?: 'Uncategorized',
            'slug' => $post->slug,
            'title' => $post->title,
            'issues' => array_values(array_unique($issues)),
            'strengths' => array_values(array_unique($strengths)),
        ];
    }
}
