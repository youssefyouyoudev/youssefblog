<?php

namespace App\Services;

class ContentService
{
    public function secureLinks(string $html): string
    {
        return preg_replace_callback('/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>/i', function (array $matches): string {
            $attributes = $matches[1];
            $url = $matches[2];
            $rels = ['noopener', 'noreferrer'];

            foreach (config('affiliate.domains', []) as $domain) {
                if (str_contains($url, $domain)) {
                    $rels[] = 'nofollow';
                    $rels[] = 'sponsored';
                }
            }

            if (! str_contains($attributes, 'rel=')) {
                $attributes .= ' rel="'.implode(' ', array_unique($rels)).'"';
            }

            return '<a '.$attributes.'>';
        }, $html) ?? $html;
    }
}
