<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SeoService
{
    public function meta(array $seo = []): array
    {
        $brand = config('brand');
        $title = $this->cleanTitle(Arr::get($seo, 'title', 'Youssef Blog — Laravel, SaaS, AI & Business Guides'));
        $description = $this->cleanDescription(Arr::get($seo, 'description', 'Practical Laravel, SaaS, AI and business guides by Youssef Youyou for developers, freelancers and Moroccan SMEs.'));
        $canonical = $this->absoluteUrl(Arr::get($seo, 'canonical', request()->fullUrl()));
        $image = Arr::get($seo, 'image', asset('assets/og-default.png'));
        $image = $this->absoluteUrl($image);
        $robots = Arr::get($seo, 'robots');
        $noindex = Arr::get($seo, 'noindex', false);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => $image,
            'type' => Arr::get($seo, 'type', 'website'),
            'keywords' => Arr::get($seo, 'keywords'),
            'robots' => $robots ?: ($noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large'),
            'noindex' => $noindex,
            'published_time' => Arr::get($seo, 'published_time'),
            'modified_time' => Arr::get($seo, 'modified_time'),
            'author' => Arr::get($seo, 'author', config('brand.name')),
            'schemas' => array_values(array_filter(array_merge(
                $this->globalSchemas(),
                Arr::wrap(Arr::get($seo, 'schemas', [])),
                [$this->breadcrumbSchema(Arr::get($seo, 'breadcrumbs', []))]
            ))),
        ];
    }

    public function postSchemas(Post $post): array
    {
        return [
            [
                '@context' => 'https://schema.org',
                '@type' => $post->schema_type ?: 'BlogPosting',
                'headline' => $post->title,
                'description' => $this->descriptionFromPost($post),
                'image' => $this->absoluteUrl($post->og_image ?: $post->featured_image ?: asset('assets/og-default.png')),
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => ($post->last_updated_at ?: $post->updated_at)?->toIso8601String(),
                'author' => $this->personSchema(),
                'publisher' => $this->organizationSchema(),
                'mainEntityOfPage' => $this->absoluteUrl(route('posts.show', $post)),
                'keywords' => $post->keywordList(),
            ],
            $this->breadcrumbSchema([
                ['name' => 'Home', 'url' => $this->absoluteUrl(route('home'))],
                ['name' => $post->category->name, 'url' => $this->absoluteUrl(route('categories.show', $post->category))],
                ['name' => $post->title, 'url' => $this->absoluteUrl(route('posts.show', $post))],
            ]),
        ];
    }

    public function blogSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => config('brand.blog_name'),
            'url' => $this->absoluteUrl(route('home')),
            'publisher' => $this->organizationSchema(),
        ];
    }

    public function personSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => config('brand.name'),
            'url' => config('brand.portfolio_url'),
            'jobTitle' => 'Senior Full-Stack Laravel Developer',
            'email' => config('brand.email'),
            'telephone' => config('brand.phone'),
            'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'MA'],
        ];
    }

    private function globalSchemas(): array
    {
        return [
            $this->organizationSchema(),
            $this->personSchema(),
            request()->routeIs('home') ? $this->blogSchema() : null,
            request()->routeIs('home') ? $this->websiteSchema() : null,
        ];
    }

    private function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('brand.blog_name'),
            'url' => $this->absoluteUrl(route('home')),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $this->absoluteUrl(route('posts.index')).'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    private function organizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('brand.name'),
            'url' => config('brand.portfolio_url'),
            'logo' => $this->absoluteUrl(asset('assets/brand/youssef-blog-logo.png')),
            'email' => config('brand.email'),
            'telephone' => config('brand.phone'),
        ];
    }

    private function breadcrumbSchema(array $items = []): array
    {
        $items = $items ?: [
            ['name' => 'Home', 'url' => $this->absoluteUrl(route('home'))],
            ['name' => str(config('app.name'))->toString(), 'url' => $this->absoluteUrl(url()->current())],
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $this->absoluteUrl($item['url']),
            ])->all(),
        ];
    }

    public function absoluteUrl(?string $url = null): string
    {
        $url = trim((string) $url);

        if ($url === '' || $url === '/') {
            return $this->publicBaseUrl();
        }

        if (Str::startsWith($url, ['mailto:', 'tel:', '#'])) {
            return $url;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            $parts = parse_url($url);

            if (($parts['host'] ?? null) && in_array($parts['host'], ['blog.youssefyouyou.com', 'localhost', '127.0.0.1'], true)) {
                $path = $parts['path'] ?? '';
                $query = isset($parts['query']) ? '?'.$parts['query'] : '';

                return $this->publicBaseUrl().rtrim('/'.ltrim($path, '/'), '/').$query;
            }

            return Str::replaceStart('http://blog.youssefyouyou.com', $this->publicBaseUrl(), $url);
        }

        return $this->publicBaseUrl().'/'.ltrim($url, '/');
    }

    public function descriptionFromPost(Post $post): string
    {
        return $this->cleanDescription($post->meta_description ?: $post->excerpt ?: $post->content);
    }

    public function categoryDescription(string $categoryName, ?string $description): string
    {
        return $this->cleanDescription($description ?: "Read practical {$categoryName} guides by Youssef Youyou covering Laravel, SaaS, AI, business systems, and digital growth.");
    }

    public function tagDescription(string $tagName): string
    {
        return $this->cleanDescription("Explore articles tagged {$tagName} on Youssef Youyou Blog, including practical guides for developers, freelancers, and digital businesses.");
    }

    public function cleanDescription(?string $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?: '';
        $text = trim($text);
        $text = preg_replace('/^(Skip to content|Menu|Home|Youssef Youyou Blog)\s+/i', '', $text) ?: $text;

        $text = Str::of($text)->squish()->toString();

        if (mb_strlen($text) > 158) {
            $text = Str::of($text)->limit(158, '')->beforeLast(' ')->trim()->toString();
        }

        return $text ?: 'Practical Laravel, SaaS, AI and business guides by Youssef Youyou for developers, freelancers and Moroccan SMEs.';
    }

    private function cleanTitle(?string $value): string
    {
        $title = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: '');

        return $title ?: 'Youssef Blog — Laravel, SaaS, AI & Business Guides';
    }

    private function publicBaseUrl(): string
    {
        return rtrim((string) config('app.url', 'https://blog.youssefyouyou.com'), '/');
    }
}
