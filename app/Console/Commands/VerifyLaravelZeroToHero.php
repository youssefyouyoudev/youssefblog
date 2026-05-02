<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\LaravelZeroToHeroContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerifyLaravelZeroToHero extends Command
{
    protected $signature = 'tutorials:verify-laravel-zero-to-hero';

    protected $description = 'Verify the Laravel From Zero to Hero tutorial posts are publish-ready for content, SEO, images, and structure.';

    public function handle(): int
    {
        $data = collect(require database_path('seeders/data/laravel-zero-to-hero.php'));
        $slugs = $data->pluck('slug');
        $posts = Post::query()
            ->whereIn('slug', $slugs)
            ->with(['category', 'tags'])
            ->orderBy('published_at')
            ->get();

        $duplicateSlugs = Post::query()
            ->whereIn('slug', $slugs)
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        $ctaCounts = $posts
            ->mapWithKeys(fn (Post $post): array => [$post->slug => $this->cta((string) $post->content)])
            ->filter()
            ->countBy();

        $seenParagraphs = [];
        $minimumWords = null;
        $failed = false;
        $rows = [];

        foreach ($posts as $post) {
            $issues = [];
            $wordCount = LaravelZeroToHeroContent::wordCount((string) $post->content);
            $minimumWords = is_null($minimumWords) ? $wordCount : min($minimumWords, $wordCount);

            if ($wordCount < LaravelZeroToHeroContent::MIN_WORDS) {
                $issues[] = 'under 2500 words';
            }

            foreach (LaravelZeroToHeroContent::bannedPhrases() as $phrase) {
                if (Str::contains(Str::lower((string) $post->content), Str::lower($phrase))) {
                    $issues[] = 'banned phrase: '.$phrase;
                }
            }

            if (blank($post->meta_title) || blank($post->seo_title)) {
                $issues[] = 'missing meta title';
            }

            if (blank($post->meta_description)) {
                $issues[] = 'missing meta description';
            }

            if (blank($post->excerpt)) {
                $issues[] = 'missing excerpt';
            }

            if (! $post->category || $post->category->slug !== 'laravel-tutorials') {
                $issues[] = 'wrong category';
            }

            if ($post->tags->count() < 3) {
                $issues[] = 'missing tags';
            }

            if (blank($post->featured_image)) {
                $issues[] = 'missing featured image';
            } elseif (! $this->localImageExists((string) $post->featured_image)) {
                $issues[] = 'featured image missing';
            }

            if ($post->status !== 'published') {
                $issues[] = 'not published';
            }

            if (blank($post->published_at) || ! $post->published_at->isToday()) {
                $issues[] = 'not posted today';
            }

            $cta = $this->cta((string) $post->content);

            if ($cta && ($ctaCounts[$cta] ?? 0) > 1) {
                $issues[] = 'repeated CTA';
            }

            foreach ($this->paragraphFingerprints((string) $post->content) as $fingerprint => $paragraph) {
                if (isset($seenParagraphs[$fingerprint])) {
                    $issues[] = 'repeated paragraph';
                    break;
                }

                $seenParagraphs[$fingerprint] = $post->slug;
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
            $rows[] = [$missingSlug, 0, '-', 'missing post'];
        }

        if ($posts->count() !== 20) {
            $failed = true;
        }

        if ($duplicateSlugs->isNotEmpty()) {
            $failed = true;
        }

        $this->table(['Post', 'Words', 'Published', 'Status'], $rows);
        $this->line('Tutorial posts checked: '.$posts->count());
        $this->line('Minimum word count found: '.($minimumWords ?? 0));
        $this->line('Posts below 2500 words: '.collect($rows)->filter(fn (array $row): bool => str_contains((string) $row[3], 'under 2500'))->count());
        $this->line('Duplicate slugs: '.($duplicateSlugs->isEmpty() ? '0' : $duplicateSlugs->implode(', ')));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function cta(string $content): ?string
    {
        if (! str_contains($content, '## Need help applying Laravel to a real project?')) {
            return null;
        }

        return Str::of($content)
            ->after('## Need help applying Laravel to a real project?')
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

    /**
     * @return array<string, string>
     */
    private function paragraphFingerprints(string $content): array
    {
        return collect(preg_split('/\n\s*\n/', $content) ?: [])
            ->map(fn (string $paragraph): string => Str::of($paragraph)->squish()->lower()->toString())
            ->filter(fn (string $paragraph): bool => ! Str::startsWith($paragraph, ['#', '-', '|', '```']))
            ->filter(fn (string $paragraph): bool => str_word_count($paragraph) >= 45)
            ->mapWithKeys(fn (string $paragraph): array => [sha1($paragraph) => $paragraph])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function internalPostLinks(string $content): array
    {
        preg_match_all('#/posts/([a-z0-9-]+)#', $content, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
