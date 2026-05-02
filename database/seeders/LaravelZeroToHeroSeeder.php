<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\LaravelZeroToHeroContent;
use App\Services\SeedImageDownloader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LaravelZeroToHeroSeeder extends Seeder
{
    public function run(): void
    {
        $posts = $this->posts();
        $images = app(SeedImageDownloader::class)->prepareMany($this->imageRecords($posts));
        $author = $this->author();
        $category = $this->category();

        foreach ($posts as $index => $postData) {
            $previous = $posts[$index - 1] ?? null;
            $next = $posts[$index + 1] ?? null;
            $content = app(LaravelZeroToHeroContent::class)->for($postData, $previous, $next);
            $image = $images[$postData['slug']] ?? null;
            $publishedAt = $this->publishedAt($index);

            $post = Post::updateOrCreate(
                ['slug' => $postData['slug']],
                [
                    'user_id' => $author->id,
                    'category_id' => $category->id,
                    'title' => $postData['title'],
                    'excerpt' => $postData['excerpt'],
                    'content' => $content,
                    'featured_image' => $image['public_url'] ?? asset('assets/brand/youssef-blog-og.png'),
                    'featured_image_alt' => $image['alt'] ?? $postData['title'],
                    'image_credit' => $image['image_credit'] ?? 'Image: Youssef Youyou branded fallback',
                    'status' => 'published',
                    'published_at' => $publishedAt,
                    'meta_title' => $postData['meta_title'],
                    'seo_title' => $postData['meta_title'],
                    'meta_description' => $postData['meta_description'],
                    'keywords' => array_values(array_unique([
                        $postData['search_intent'],
                        'Laravel tutorial',
                        'Laravel from zero to hero',
                        ...$postData['tags'],
                    ])),
                    'faqs' => $this->faqs($postData),
                    'canonical_url' => url('/posts/'.$postData['slug']),
                    'og_image' => $image['public_url'] ?? asset('assets/brand/youssef-blog-og.png'),
                    'reading_time' => max(5, (int) ceil(Str::wordCount(strip_tags($content)) / 220)),
                    'views' => 0,
                    'ad_clicks' => 0,
                    'affiliate_clicks' => 0,
                    'is_featured' => $index < 2,
                    'last_updated_at' => $publishedAt,
                    'schema_type' => 'TechArticle',
                ],
            );

            $tagIds = collect($postData['tags'])
                ->merge(['Laravel Tutorials', 'Youssef Youyou'])
                ->unique()
                ->map(fn (string $name): int => Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)
                ->all();

            $post->tags()->sync($tagIds);
        }
    }

    public function posts(): array
    {
        return require database_path('seeders/data/laravel-zero-to-hero.php');
    }

    /**
     * @param  array<int, array<string, mixed>>  $posts
     * @return array<int, array<string, string>>
     */
    public function imageRecords(array $posts): array
    {
        $existing = collect(json_decode((string) file_get_contents(database_path('seeders/data/seeded-post-images.json')), true))
            ->keyBy('post_slug');

        return collect($posts)->map(function (array $post) use ($existing): array {
            $record = $existing[$post['image_slug']];

            return [
                ...$record,
                'post_title' => $post['title'],
                'post_slug' => $post['slug'],
                'concept' => 'Laravel tutorial cover image for '.$post['search_intent'],
                'local_image_path' => 'blog/seeded-images/'.$post['slug'].'.jpg',
                'alt' => 'Laravel tutorial cover for '.$post['title'],
            ];
        })->all();
    }

    private function publishedAt(int $index)
    {
        return now()
            ->startOfDay()
            ->setTime(6, 0)
            ->addMinutes($index * 20);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'laravel-tutorials'],
            [
                'name' => 'Laravel Tutorials',
                'description' => 'Practical Laravel tutorials for developers learning to build real web applications, dashboards, APIs, and business tools.',
                'seo_title' => 'Laravel Tutorials by Youssef Youyou',
                'meta_description' => 'Practical Laravel tutorials for developers learning routes, controllers, Eloquent, APIs, testing, security, performance, and deployment.',
            ],
        );
    }

    private function author(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@youssefyouyou.com'],
            [
                'name' => 'Youssef Youyou',
                'password' => Hash::make(Str::random(32)),
                'role' => 'admin',
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<int, array<string, string>>
     */
    private function faqs(array $post): array
    {
        return [
            [
                'question' => 'Is this Laravel tutorial beginner friendly?',
                'answer' => 'Yes. It explains '.$post['search_intent'].' in practical language and connects the concept to real Laravel projects.',
            ],
            [
                'question' => 'Can I use this in client projects?',
                'answer' => 'Yes, but use the examples as a learning base. Real client projects need validation, authorization, testing, deployment checks, and maintainable structure.',
            ],
        ];
    }
}
