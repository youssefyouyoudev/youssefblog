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
        $title = Arr::get($seo, 'title', $brand['blog_name'].' | Finance, Tech & AI');
        $description = Arr::get($seo, 'description', 'Smart finance, tech, AI, Laravel, and online business guides for builders.');
        $canonical = Arr::get($seo, 'canonical', url()->current());
        $image = Arr::get($seo, 'image', asset('assets/og-default.png'));
        $image = Str::startsWith($image, ['http://', 'https://']) ? $image : url($image);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => $image,
            'type' => Arr::get($seo, 'type', 'website'),
            'keywords' => Arr::get($seo, 'keywords'),
            'noindex' => Arr::get($seo, 'noindex', false),
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
                'description' => $post->meta_description ?: $post->excerpt,
                'image' => $post->og_image ?: $post->featured_image ?: asset('assets/og-default.png'),
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => ($post->last_updated_at ?: $post->updated_at)?->toIso8601String(),
                'author' => $this->personSchema(),
                'publisher' => $this->organizationSchema(),
                'mainEntityOfPage' => route('posts.show', $post),
                'keywords' => $post->keywordList(),
            ],
            $this->breadcrumbSchema([
                ['name' => 'Home', 'url' => route('home')],
                ['name' => $post->category->name, 'url' => route('categories.show', $post->category)],
                ['name' => $post->title, 'url' => route('posts.show', $post)],
            ]),
        ];
    }

    public function blogSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Blog',
            'name' => config('brand.blog_name'),
            'url' => route('home'),
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
            'url' => route('home'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('posts.index').'?q={search_term_string}',
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
            'logo' => asset('assets/brand/youssef-blog-logo.png'),
            'email' => config('brand.email'),
            'telephone' => config('brand.phone'),
        ];
    }

    private function breadcrumbSchema(array $items = []): array
    {
        $items = $items ?: [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => str(config('app.name'))->toString(), 'url' => url()->current()],
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ])->all(),
        ];
    }
}
