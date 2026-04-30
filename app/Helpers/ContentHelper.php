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

    public static function processPlainLinks(string $content): string
    {
        $content = preg_replace_callback('/(?<!["\'>=])(https?:\/\/[^\s<]+)/i', function (array $matches): string {
            $url = rtrim($matches[1], '.,)');
            $tail = substr($matches[1], strlen($url));

            return '<a href="'.$url.'">'.$url.'</a>'.$tail;
        }, $content) ?? $content;

        return preg_replace_callback('/(?<!["\'>=])\b(\/(?:posts|category|tag|best)\/[a-z0-9\-\/]+)\b/i', function (array $matches): string {
            $url = $matches[1];
            $label = trim(str_replace(['-', '/'], [' ', ' / '], $url));

            return '<a href="'.$url.'">'.e($label).'</a>';
        }, $content) ?? $content;
    }

    public static function processMarkdownLinks(string $content): string
    {
        return preg_replace_callback('/\[([^\]\n]{1,90})\]\(((?:\/(?:posts|category|tag|best)\/[a-z0-9\-\/]+)|(?:https:\/\/youssefyouyou\.com[^\s)]*))\)/i', function (array $matches): string {
            return '<a href="'.e($matches[2]).'">'.e($matches[1]).'</a>';
        }, $content) ?? $content;
    }

    public static function wordCount(string $content): int
    {
        return str_word_count(strip_tags($content));
    }

    public static function process(string $content, string $variant = 'freelance'): string
    {
        return self::processAffiliateLinks(
            self::processExternalLinks(
                self::processPlainLinks(
                    self::processMarkdownLinks(
                        self::enhanceCodeBlocks($content)
                    )
                )
            )
        );
    }
}
