<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ProductionPostContent;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class VerifyContentQuality extends Command
{
    protected $signature = 'blog:verify-content-quality';

    protected $description = 'Verify seeded production posts meet word count, SEO, publishing, image, slug, CTA, and phrase quality checks.';

    public function handle(): int
    {
        $slugs = collect(array_keys(require database_path('seeders/data/production-post-content.php')));
        $posts = Post::query()
            ->whereIn('slug', $slugs)
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

            if ($wordCount < ProductionPostContent::MIN_WORDS) {
                $issues[] = 'under 3000 words';
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
            }

            if (blank($post->published_at)) {
                $issues[] = 'missing published_at';
            } elseif ($post->published_at->isFuture()) {
                $issues[] = 'future published_at';
            }

            $cta = $this->cta((string) $post->content);

            if ($cta && ($ctaCounts[$cta] ?? 0) > 1) {
                $issues[] = 'repeated CTA detected';
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

        $this->table(['Post', 'Words', 'Published', 'Status'], $rows);
        $this->line('Total seeded published posts checked: '.$posts->count());
        $this->line('Minimum word count found: '.($minimumWords ?? 0));
        $this->line('Posts below 3000 words: '.collect($rows)->filter(fn (array $row): bool => str_contains((string) $row[3], 'under 3000'))->count());
        $this->line('Duplicate slugs: '.($duplicateSlugs->isEmpty() ? '0' : $duplicateSlugs->implode(', ')));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function cta(string $content): ?string
    {
        if (! str_contains($content, '## Need help planning this properly?')) {
            return null;
        }

        return Str::of($content)
            ->after('## Need help planning this properly?')
            ->squish()
            ->toString();
    }
}
