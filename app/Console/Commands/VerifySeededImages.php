<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerifySeededImages extends Command
{
    protected $signature = 'blog:verify-seeded-images';

    protected $description = 'Verify seeded blog post images, local files, rendered storage paths, and attribution records.';

    public function handle(): int
    {
        $records = $this->imageRecords();
        $recordsBySlug = collect($records)->keyBy('post_slug');
        $posts = Post::query()
            ->whereIn('slug', $recordsBySlug->keys())
            ->where('status', 'published')
            ->orderBy('published_at')
            ->get();

        $stats = [
            'total' => $posts->count(),
            'valid' => 0,
            'missing' => 0,
            'broken' => 0,
            'external' => 0,
            'attribution_missing' => 0,
        ];

        $rows = [];

        foreach ($posts as $post) {
            $record = $recordsBySlug->get($post->slug);
            $path = $this->storagePathFromUrl((string) $post->featured_image);
            $issues = [];

            if (blank($post->featured_image)) {
                $issues[] = 'missing featured_image';
                $stats['missing']++;
            }

            if (! $path) {
                $issues[] = 'external or non-storage path';
                $stats['external']++;
            } elseif (! Storage::disk('public')->exists($path)) {
                $issues[] = 'file missing';
                $stats['broken']++;
            } else {
                $contents = Storage::disk('public')->get($path);
                $info = is_string($contents) ? @getimagesizefromstring($contents) : false;
                $publicPath = public_path('storage/'.str_replace('\\', '/', $path));

                if ($info === false || ! is_readable(Storage::disk('public')->path($path))) {
                    $issues[] = 'file unreadable';
                    $stats['broken']++;
                } elseif (($info[0] ?? 0) < 1200 || ($info[1] ?? 0) < 630) {
                    $issues[] = 'image too small';
                    $stats['broken']++;
                } elseif (! file_exists($publicPath)) {
                    $issues[] = 'storage link missing';
                    $stats['broken']++;
                }
            }

            foreach (['source_url', 'author', 'license', 'local_image_path'] as $required) {
                if (blank($record[$required] ?? null)) {
                    $issues[] = 'attribution missing';
                    $stats['attribution_missing']++;
                    break;
                }
            }

            if ($issues === []) {
                $stats['valid']++;
            }

            $rows[] = [
                Str::limit($post->title, 44),
                $path ?: $post->featured_image,
                $record['source'] ?? 'missing',
                $record['author'] ?? 'missing',
                $issues === [] ? 'valid' : implode(', ', $issues),
            ];
        }

        $missingSeededPosts = $recordsBySlug->keys()->diff($posts->pluck('slug'));

        foreach ($missingSeededPosts as $slug) {
            $stats['missing']++;
            $rows[] = [$slug, '-', '-', '-', 'seeded post missing'];
        }

        $this->table(['Post', 'Image path', 'Source', 'Author', 'Status'], $rows);
        $this->line('Total posts checked: '.$stats['total']);
        $this->line('Valid images: '.$stats['valid']);
        $this->line('Missing images/posts: '.$stats['missing']);
        $this->line('Broken images: '.$stats['broken']);
        $this->line('External images still used: '.$stats['external']);
        $this->line('Attribution missing: '.$stats['attribution_missing']);

        return ($stats['missing'] + $stats['broken'] + $stats['external'] + $stats['attribution_missing']) === 0
            ? self::SUCCESS
            : self::FAILURE;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function imageRecords(): array
    {
        $path = database_path('seeders/data/seeded-post-images.json');
        $records = json_decode((string) file_get_contents($path), true);

        return is_array($records) ? $records : [];
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
}
