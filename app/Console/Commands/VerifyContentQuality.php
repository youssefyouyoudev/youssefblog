<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ProductionPostContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerifyContentQuality extends Command
{
    private const MIN_WORDS = 2500;

    protected $signature = 'blog:verify-content-quality';

    protected $description = 'Verify seeded production posts meet 2500-word, SEO, image, slug, CTA, phrase, and repetition quality checks.';

    public function handle(): int
    {
        $productionSlugs = collect(array_keys(require database_path('seeders/data/production-post-content.php')));
        $scheduledData = collect(require database_path('seeders/data/scheduled-posts.php'));
        $scheduledSlugs = $scheduledData->pluck('slug');
        $slugs = $productionSlugs->merge($scheduledSlugs)->unique()->values();
        $posts = Post::query()
            ->whereIn('slug', $slugs)
            ->with('category', 'tags')
            ->orderBy('published_at')
            ->get();

        $duplicateSlugs = Post::query()
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        $ctaCounts = $posts
            ->mapWithKeys(fn (Post $post): array => [$post->slug => $this->cta($post->content)])
            ->filter()
            ->countBy();

        $rows = [];
        $failed = false;
        $minimumWords = null;

        foreach ($posts as $post) {
            $wordCount = ProductionPostContent::wordCount((string) $post->content);
            $minimumWords = is_null($minimumWords) ? $wordCount : min($minimumWords, $wordCount);
            $issues = [];

            if ($wordCount < self::MIN_WORDS) {
                $issues[] = 'under 2500 words';
            }

            foreach (ProductionPostContent::bannedPhrases() as $phrase) {
                if (Str::contains(Str::lower($post->content), Str::lower($phrase))) {
                    $issues[] = 'banned phrase: '.$phrase;
                }
            }

            if (blank($post->meta_title) || blank($post->seo_title)) {
                $issues[] = 'missing meta_title';
            }

            if (blank($post->meta_description)) {
                $issues[] = 'missing meta_description';
            }

            if (blank($post->excerpt)) {
                $issues[] = 'missing excerpt';
            }

            if (blank($post->featured_image)) {
                $issues[] = 'missing featured image';
            } elseif (! $this->localImageExists((string) $post->featured_image)) {
                $issues[] = 'featured image file missing';
            }

            if (blank($post->published_at)) {
                $issues[] = 'missing published_at';
            } elseif ($post->published_at->isFuture() && $post->status !== 'scheduled') {
                $issues[] = 'future published_at';
            }

            $cta = $this->cta((string) $post->content);

            if ($cta && ($ctaCounts[$cta] ?? 0) > 1) {
                $issues[] = 'repeated CTA detected';
            }

            if (! $post->category) {
                $issues[] = 'missing category';
            }

            if ($post->tags->isEmpty()) {
                $issues[] = 'missing tags';
            }

            if ($this->hasEmptySection((string) $post->content)) {
                $issues[] = 'empty section';
            }

            if ($this->hasDuplicateBlocks((string) $post->content)) {
                $issues[] = 'repeated paragraph or section';
            }

            foreach ($this->internalPostLinks((string) $post->content) as $slug) {
                if (! $slugs->contains($slug) && ! Post::where('slug', $slug)->exists()) {
                    $issues[] = 'broken internal link: '.$slug;
                    break;
                }
            }

            if ($issues !== []) {
                $failed = true;
            }

            $rows[] = [
                Str::limit($post->title, 48),
                $wordCount,
                $post->published_at?->format('Y-m-d H:i') ?? '-',
                $issues === [] ? 'valid' : implode(', ', $issues),
            ];
        }

        foreach ($slugs->diff($posts->pluck('slug')) as $missingSlug) {
            $failed = true;
            $rows[] = [$missingSlug, 0, '-', 'seeded post missing'];
        }

        if ($duplicateSlugs->isNotEmpty()) {
            $failed = true;
        }

        $scheduledPosts = $posts->whereIn('slug', $scheduledSlugs);
        $scheduledIssues = $this->scheduledIssues($scheduledPosts, $scheduledData);

        if ($scheduledIssues !== []) {
            $failed = true;
        }

        $visibleFutureScheduled = Post::published()
            ->whereIn('slug', $scheduledSlugs)
            ->where('published_at', '>', now())
            ->count();

        if ($visibleFutureScheduled > 0) {
            $failed = true;
        }

        $this->table(['Post', 'Words', 'Published', 'Status'], $rows);
        $this->line('Total seeded published posts checked: '.$posts->count());
        $this->line('Minimum word count found: '.($minimumWords ?? 0));
        $this->line('Posts below 2500 words: '.collect($rows)->filter(fn (array $row): bool => str_contains((string) $row[3], 'under 2500'))->count());
        $this->line('Duplicate slugs: '.($duplicateSlugs->isEmpty() ? '0' : $duplicateSlugs->implode(', ')));
        $this->line('Scheduled posts found: '.$scheduledPosts->count());
        $this->line('Future scheduled posts visible publicly: '.$visibleFutureScheduled);

        foreach ($scheduledIssues as $issue) {
            $this->error($issue);
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function cta(string $content): ?string
    {
        $heading = str_contains($content, '## Need help planning this properly?')
            ? '## Need help planning this properly?'
            : (str_contains($content, '## Need help with this?') ? '## Need help with this?' : null);

        if (! $heading) {
            return null;
        }

        return Str::of($content)
            ->after($heading)
            ->squish()
            ->toString();
    }

    private function localImageExists(string $url): bool
    {
        $path = $this->storagePathFromUrl($url);

        return $path !== null && Storage::disk('public')->exists($path) && is_readable(Storage::disk('public')->path($path));
    }

    private function storagePathFromUrl(string $url): ?string
    {
        if (Str::startsWith($url, '/storage/')) {
            return Str::after($url, '/storage/');
        }

        $diskUrl = rtrim((string) config('filesystems.disks.public.url'), '/').'/';

        if (Str::startsWith($url, $diskUrl)) {
            return Str::after($url, $diskUrl);
        }

        return null;
    }

    private function hasEmptySection(string $content): bool
    {
        $parts = preg_split('/\n##\s+/', $content) ?: [];

        return collect($parts)
            ->skip(1)
            ->contains(function (string $part): bool {
                $heading = strtok($part, PHP_EOL) ?: '';

                if (Str::startsWith($heading, 'Need help')) {
                    return false;
                }

                preg_match_all('/^\s*-\s+\S+/m', $part, $listItems);

                if (count($listItems[0] ?? []) >= 2) {
                    return false;
                }

                $body = Str::of($part)
                    ->replaceMatches('/^.+\n/', '')
                    ->replaceMatches('/\n#{3,}\s+.+/', "\n")
                    ->squish()
                    ->toString();

                return ProductionPostContent::wordCount($body) < 20;
            });
    }

    private function hasDuplicateBlocks(string $content): bool
    {
        $blocks = collect(preg_split('/\n\s*\n/', $content) ?: [])
            ->map(fn (string $block): string => Str::of($block)->squish()->lower()->toString())
            ->filter(fn (string $block): bool => ! Str::startsWith($block, ['#', '-', '|']))
            ->filter(fn (string $block): bool => str_word_count($block) >= 35)
            ->values();

        return $blocks->count() !== $blocks->unique()->count();
    }

    /**
     * @return array<int, string>
     */
    private function internalPostLinks(string $content): array
    {
        preg_match_all('#/posts/([a-z0-9-]+)#', $content, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    private function scheduledIssues($scheduledPosts, $scheduledData): array
    {
        $issues = [];

        if ($scheduledPosts->count() !== 30) {
            $issues[] = 'Expected exactly 30 scheduled posts, found '.$scheduledPosts->count().'.';
        }

        $nonScheduled = $scheduledPosts->where('status', '!=', 'scheduled');

        if ($nonScheduled->isNotEmpty()) {
            $issues[] = 'Some future editorial posts are not marked scheduled.';
        }

        $dates = $scheduledPosts
            ->groupBy(fn (Post $post): string => $post->published_at?->toDateString() ?? 'missing')
            ->map->count();

        if ($dates->count() !== 10 || $dates->contains(fn (int $count): bool => $count !== 3)) {
            $issues[] = 'Scheduled posts are not exactly 3 per day for 10 days.';
        }

        $expectedDates = collect(range(1, 10))->map(fn (int $day): string => now()->addDays($day)->toDateString());

        if ($expectedDates->diff($dates->keys())->isNotEmpty() || collect($dates->keys())->diff($expectedDates)->isNotEmpty()) {
            $issues[] = 'Scheduled dates are not within the next 10 days.';
        }

        $expectedTimes = ['09:00', '13:30', '18:45'];
        $badTimes = $scheduledPosts->filter(fn (Post $post): bool => ! in_array($post->published_at?->format('H:i'), $expectedTimes, true));

        if ($badTimes->isNotEmpty()) {
            $issues[] = 'Some scheduled posts do not use the required publishing times.';
        }

        return $issues;
    }
}
