<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\ScheduledPostContent;
use App\Services\SeedImageDownloader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionScheduledPostsSeeder extends Seeder
{
    public function run(): void
    {
        $posts = $this->posts();
        $images = app(SeedImageDownloader::class)->prepareMany($this->imageRecords($posts));
        $author = $this->author();

        foreach ($posts as $postData) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($postData['category'])],
                [
                    'name' => $postData['category'],
                    'description' => $postData['category'].' guides for business owners, founders, and teams planning practical web systems.',
                    'seo_title' => $postData['category'].' Guides by Youssef Youyou',
                    'meta_description' => Str::limit($postData['category'].' articles for startups, small businesses, and founders planning websites, SaaS MVPs, dashboards, CRM systems, and automation tools.', 155, ''),
                ],
            );

            $content = app(ScheduledPostContent::class)->for($postData);
            $image = $images[$postData['slug']] ?? null;
            $scheduledAt = $this->scheduledAt($postData['day'], $postData['slot']);

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
                    'status' => 'scheduled',
                    'published_at' => $scheduledAt,
                    'meta_title' => $postData['meta_title'],
                    'seo_title' => $postData['meta_title'],
                    'meta_description' => $postData['meta_description'],
                    'keywords' => $postData['keywords'],
                    'faqs' => $this->faqs($postData),
                    'canonical_url' => url('/posts/'.$postData['slug']),
                    'og_image' => $image['public_url'] ?? asset('assets/brand/youssef-blog-og.png'),
                    'reading_time' => max(4, (int) ceil(Str::wordCount(strip_tags($content)) / 220)),
                    'views' => 0,
                    'ad_clicks' => 0,
                    'affiliate_clicks' => 0,
                    'is_featured' => false,
                    'last_updated_at' => $scheduledAt,
                    'schema_type' => 'BlogPosting',
                ],
            );

            $tags = collect($postData['tags'])->map(function (string $name): int {
                return Tag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
            });

            $post->tags()->sync($tags->all());
        }
    }

    public function scheduledAt(int $day, int $slot)
    {
        $times = [[9, 0], [13, 30], [18, 45]];
        [$hour, $minute] = $times[$slot];

        return now()->addDays($day)->setTime($hour, $minute);
    }

    public function posts(): array
    {
        return require database_path('seeders/data/scheduled-posts.php');
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
                'concept' => 'Scheduled post cover for '.$post['search_intent'],
                'local_image_path' => 'blog/seeded-images/'.$post['slug'].'.jpg',
            ];
        })->all();
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

    private function faqs(array $post): array
    {
        return collect($post['faq'])
            ->map(fn (array $faq): array => ['question' => $faq[0], 'answer' => $faq[1]])
            ->values()
            ->all();
    }
}
