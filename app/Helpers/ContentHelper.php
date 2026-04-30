<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Blade;

class ContentHelper
{
    public static function processExternalLinks(string $content): string
    {
        return preg_replace_callback('/<a\s+([^>]*href=["\'](https?:\/\/[^"\']+)["\'][^>]*)>/i', function (array $matches): string {
            $attributes = $matches[1];

            if (! str_contains($attributes, 'rel=')) {
                $attributes .= ' rel="noopener noreferrer"';
            }

            return '<a '.$attributes.'>';
        }, $content) ?? $content;
    }

    public static function processAffiliateLinks(string $content): string
    {
        return preg_replace_callback('/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>/i', function (array $matches): string {
            $attributes = $matches[1];
            $url = $matches[2];

            foreach (config('affiliate.domains', []) as $domain) {
                if (str_contains($url, $domain)) {
                    $attributes = preg_replace('/rel=["\'][^"\']*["\']/', '', $attributes) ?? $attributes;
                    $attributes .= ' rel="nofollow sponsored noopener noreferrer"';
                    break;
                }
            }

            return '<a '.$attributes.'>';
        }, $content) ?? $content;
    }

    public static function injectCtaBlocks(string $content, string $variant = 'freelance'): string
    {
        $count = 0;

        return preg_replace_callback('/<\/p>/i', function () use (&$count, $variant): string {
            $count++;

            return $count % 4 === 0
                ? '</p>'.Blade::render('<x-cta variant="'.$variant.'" class="my-8" />')
                : '</p>';
        }, $content) ?? $content;
    }

    public static function enhanceCodeBlocks(string $content): string
    {
        return preg_replace('/<pre>(.*?)<\/pre>/is', '<div class="code-shell relative my-6"><button type="button" class="copy-code absolute right-3 top-3 rounded-lg bg-white/10 px-3 py-1 text-xs font-bold text-white">Copy</button><pre>$1</pre></div>', $content) ?? $content;
    }

    public static function wordCount(string $content): int
    {
        return str_word_count(strip_tags($content));
    }

    public static function process(string $content, string $variant = 'freelance'): string
    {
        return self::processAffiliateLinks(
            self::processExternalLinks(
                self::enhanceCodeBlocks($content)
            )
        );
    }
}
