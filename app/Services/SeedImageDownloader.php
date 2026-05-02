<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SeedImageDownloader
{
    private const DISK = 'public';

    private const DIRECTORY = 'blog/seeded-images';

    private const MAX_BYTES = 8_388_608;

    private const COVER_WIDTH = 1200;

    private const COVER_HEIGHT = 630;

    /**
     * @param  array<int, array<string, string>>  $records
     * @return array<string, array<string, mixed>>
     */
    public function prepareMany(array $records): array
    {
        return collect($records)
            ->mapWithKeys(fn (array $record): array => [
                $record['post_slug'] => $this->prepare($record),
            ])
            ->all();
    }

    /**
     * @param  array<string, string>  $record
     * @return array<string, mixed>
     */
    public function prepare(array $record): array
    {
        $slug = Str::slug((string) $record['post_slug']);
        $path = $this->safePath($slug);

        try {
            if (! $this->isValidLocalImage($path)) {
                $this->downloadAndStore($record, $path);
            }

            return $this->result($record, $path, false);
        } catch (RuntimeException) {
            $this->generateFallback($path, (string) $record['post_title']);

            return $this->result([
                ...$record,
                'source' => 'Generated fallback',
                'source_url' => url('/storage/'.$path),
                'author' => 'Youssef Youyou',
                'author_url' => 'https://youssefyouyou.com',
                'license' => 'Owned branded fallback',
                'license_url' => 'https://youssefyouyou.com',
            ], $path, true);
        }
    }

    private function downloadAndStore(array $record, string $path): void
    {
        $url = (string) Arr::get($record, 'download_url');

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Invalid image URL.');
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! in_array($host, ['unsplash.com', 'images.unsplash.com'], true)) {
            throw new RuntimeException('Image host is not allowed.');
        }

        $response = Http::timeout(30)
            ->connectTimeout(10)
            ->retry(2, 300)
            ->withHeaders(['User-Agent' => 'YoussefBlogSeeder/1.0'])
            ->get($url);

        if (! $response->ok()) {
            throw new RuntimeException('Image request failed.');
        }

        $contentType = Str::of((string) $response->header('Content-Type'))->before(';')->trim()->lower()->toString();

        if ($contentType !== '' && ! in_array($contentType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Response content type is not an allowed image.');
        }

        $body = $response->body();

        if ($body === '' || strlen($body) > (self::MAX_BYTES * 2)) {
            throw new RuntimeException('Image file size is not acceptable.');
        }

        $info = @getimagesizefromstring($body);

        if ($info === false) {
            throw new RuntimeException('Downloaded file is not an image.');
        }

        $mime = $info['mime'] ?? null;

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('Image mime type is not allowed.');
        }

        if (($info[0] ?? 0) < self::COVER_WIDTH || ($info[1] ?? 0) < 500) {
            throw new RuntimeException('Image is too small for a blog cover.');
        }

        $this->storeCover($body, $path);
    }

    private function storeCover(string $body, string $path): void
    {
        $source = @imagecreatefromstring($body);

        if (! $source) {
            throw new RuntimeException('Unable to decode image.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetRatio = self::COVER_WIDTH / self::COVER_HEIGHT;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($sourceHeight - $cropHeight) / 2);
        }

        $cover = imagecreatetruecolor(self::COVER_WIDTH, self::COVER_HEIGHT);
        imagecopyresampled($cover, $source, 0, 0, $cropX, $cropY, self::COVER_WIDTH, self::COVER_HEIGHT, $cropWidth, $cropHeight);

        ob_start();
        imagejpeg($cover, null, 84);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($cover);

        if (! is_string($jpeg) || strlen($jpeg) > self::MAX_BYTES) {
            throw new RuntimeException('Optimized image is not acceptable.');
        }

        Storage::disk(self::DISK)->put($path, $jpeg);
    }

    private function generateFallback(string $path, string $title): void
    {
        $image = imagecreatetruecolor(self::COVER_WIDTH, self::COVER_HEIGHT);
        $background = imagecolorallocate($image, 13, 20, 33);
        $panel = imagecolorallocate($image, 21, 31, 50);
        $accent = imagecolorallocate($image, 16, 185, 129);
        $muted = imagecolorallocate($image, 148, 163, 184);
        $white = imagecolorallocate($image, 248, 250, 252);

        imagefilledrectangle($image, 0, 0, self::COVER_WIDTH, self::COVER_HEIGHT, $background);
        imagefilledrectangle($image, 70, 70, self::COVER_WIDTH - 70, self::COVER_HEIGHT - 70, $panel);
        imagefilledrectangle($image, 70, 70, 90, self::COVER_HEIGHT - 70, $accent);
        imagefilledellipse($image, 1010, 140, 260, 260, imagecolorallocate($image, 8, 145, 178));
        imagefilledellipse($image, 1080, 500, 360, 360, imagecolorallocate($image, 4, 120, 87));

        imagestring($image, 5, 120, 130, 'Youssef Youyou', $accent);
        imagestring($image, 4, 120, 160, 'Web Development, SaaS, Laravel & Automation', $muted);

        $lines = explode("\n", wordwrap($title, 42));
        $y = 250;

        foreach (array_slice($lines, 0, 5) as $line) {
            imagestring($image, 5, 120, $y, $line, $white);
            $y += 34;
        }

        imagestring($image, 4, 120, 500, 'blog.youssefyouyou.com', $muted);

        ob_start();
        imagejpeg($image, null, 88);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        if (! is_string($jpeg)) {
            throw new RuntimeException('Unable to generate fallback image.');
        }

        Storage::disk(self::DISK)->put($path, $jpeg);
    }

    private function safePath(string $slug): string
    {
        return self::DIRECTORY.'/'.$slug.'.jpg';
    }

    private function isValidLocalImage(string $path): bool
    {
        if (! Storage::disk(self::DISK)->exists($path)) {
            return false;
        }

        $content = Storage::disk(self::DISK)->get($path);
        $info = is_string($content) ? @getimagesizefromstring($content) : false;

        return $info !== false
            && ($info[0] ?? 0) >= self::COVER_WIDTH
            && ($info[1] ?? 0) >= self::COVER_HEIGHT
            && in_array($info['mime'] ?? null, ['image/jpeg', 'image/png', 'image/webp'], true)
            && strlen($content) <= self::MAX_BYTES;
    }

    private function result(array $record, string $path, bool $fallback): array
    {
        return [
            ...$record,
            'local_image_path' => $path,
            'public_url' => Storage::disk(self::DISK)->url($path),
            'image_credit' => $fallback
                ? 'Image: branded fallback by Youssef Youyou'
                : sprintf('Photo by %s on %s (%s)', $record['author'], $record['source'], $record['license']),
            'used_fallback' => $fallback,
        ];
    }
}
