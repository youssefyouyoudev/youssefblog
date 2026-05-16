<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure a default author user exists
        $author = User::firstOrCreate(
            ['email' => 'youssef@youyoudev.com'],
            [
                'name'              => 'Youssef Youyou',
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $dataFiles = [
            // AI & Business Automation (posts 1–8)
            'blog_posts_ai_business.json',
            'blog_posts_ai_business_3_4.json',
            'blog_posts_ai_business_5_6.json',
            'blog_posts_ai_business_7_8.json',
            // E-Commerce (posts 9–12)
            'blog_posts_ecommerce_9_10.json',
            'blog_posts_ecommerce_11_12.json',
            // Freelance & Digital Business (posts 13–16)
            'blog_posts_freelance_13_14.json',
            'blog_posts_freelance_15_16.json',
            // Laravel & Web Development (posts 17–20)
            'blog_posts_laravel_17_18.json',
            'blog_posts_laravel_19_20.json',
            // SaaS & MVP Development (posts 21–24)
            'blog_posts_saas_21_22.json',
            'blog_posts_saas_23_24.json',
            // Laravel Tutorials & Best Practices (posts 25–28)
            'blog_posts_laravel_25_26.json',
            'blog_posts_laravel_27_28.json',
            // Web Development Trends (posts 29–32)
            'blog_posts_webdev_29_30.json',
            'blog_posts_webdev_31_32.json',
        ];

        $seededCount = 0;

        foreach ($dataFiles as $file) {
            $path = database_path("seeders/data/{$file}");

            if (! file_exists($path)) {
                $this->command->warn("⚠  Missing data file: {$file}");
                continue;
            }

            $posts = json_decode(file_get_contents($path), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("✗ Invalid JSON in {$file}: " . json_last_error_msg());
                continue;
            }

            foreach ($posts as $data) {
                // ── Category ────────────────────────────────────────────────
                $categoryName = $data['category'] ?? 'General';
                $category     = Category::firstOrCreate(
                    ['slug' => Str::slug($categoryName)],
                    [
                        'name'             => $categoryName,
                        'description'      => "Articles about {$categoryName}.",
                        'seo_title'        => "{$categoryName} Articles | Youssef Youyou Dev",
                        'meta_description' => "Expert articles and guides on {$categoryName} from Youssef Youyou Dev.",
                    ]
                );

                // ── Map status ───────────────────────────────────────────────
                $rawStatus = $data['publication_status'] ?? 'draft';
                $status    = match ($rawStatus) {
                    'published' => 'published',
                    'scheduled' => 'scheduled',
                    default     => 'published', // treat drafts as published so content is visible
                };

                // ── Build HTML content from content_structure ────────────────
                $content = $this->buildContent($data);

                // ── SEO fields ───────────────────────────────────────────────
                $seo         = $data['seo'] ?? [];
                $keywords    = array_merge(
                    $data['tags'] ?? [],
                    $seo['secondary_keywords'] ?? []
                );
                $metaTitle   = isset($seo['h1'])
                    ? $seo['h1'] . ' | Youssef Youyou Dev'
                    : ($data['title'] . ' | Youssef Youyou Dev');

                // ── Featured image ───────────────────────────────────────────
                $featuredImg    = $data['featured_image'] ?? [];
                $featuredImgAlt = $featuredImg['alt_text'] ?? $data['title'];
                $imageCredit    = $featuredImg['credits'] ?? null;

                // ── Upsert post ──────────────────────────────────────────────
                $post = Post::withTrashed()->updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'user_id'           => $author->id,
                        'category_id'       => $category->id,
                        'title'             => $data['title'],
                        'slug'              => $data['slug'],
                        'excerpt'           => $data['excerpt'] ?? '',
                        'content'           => $content,
                        'featured_image'    => $featuredImg['stock_source'] ?? null,
                        'featured_image_alt' => $featuredImgAlt,
                        'image_credit'      => $imageCredit,
                        'status'            => $status,
                        'published_at'      => $data['published_at'] ?? now(),
                        'meta_title'        => $metaTitle,
                        'seo_title'         => $metaTitle,
                        'meta_description'  => $data['meta_description'] ?? null,
                        'keywords'          => array_values(array_unique(array_filter($keywords))),
                        'canonical_url'     => null,
                        'og_image'          => $featuredImg['stock_source'] ?? null,
                        'is_featured'       => false,
                        'schema_type'       => $data['schema_markup']['type'] ?? 'BlogPosting',
                        'deleted_at'        => null,
                    ]
                );

                // Restore if soft-deleted
                if ($post->trashed()) {
                    $post->restore();
                }

                // ── Tags ─────────────────────────────────────────────────────
                $tagIds = [];
                foreach (($data['tags'] ?? []) as $tagName) {
                    $tag      = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);

                $seededCount++;
            }

            $this->command->info("✓ Seeded " . count($posts) . " posts from {$file}");
        }

        $this->command->info("✅ BlogPostSeeder complete — {$seededCount} posts upserted.");
    }

    /**
     * Build proper HTML content from the JSON content_structure field.
     * Falls back to a rich stub when the structure data is sparse.
     */
    private function buildContent(array $data): string
    {
        $cs      = $data['content_structure'] ?? [];
        $seo     = $data['seo'] ?? [];
        $title   = $data['title'] ?? '';
        $excerpt = $data['excerpt'] ?? '';

        $h2s     = $seo['h2_subheadings'] ?? [];
        $refs    = $data['references'] ?? [];
        $cta     = $data['cta_strategy'] ?? [];
        $stats   = $refs['statistics'] ?? [];
        $quotes  = $refs['expert_quotes'] ?? [];
        $tools   = $refs['tools_mentioned'] ?? [];

        $html = '';

        // ── Introduction ─────────────────────────────────────────────────────
        $intro = $cs['introduction'] ?? $excerpt;
        $html .= "<p>{$intro}</p>\n\n";

        // ── Sections from h2 subheadings ─────────────────────────────────────
        $sectionKeys = ['section_1', 'section_2', 'section_3', 'section_4', 'section_5'];
        foreach ($h2s as $index => $heading) {
            $html .= "<h2>{$heading}</h2>\n";

            $sectionKey = $sectionKeys[$index] ?? null;
            if ($sectionKey && isset($cs[$sectionKey])) {
                $html .= "<p>{$cs[$sectionKey]}</p>\n\n";
            } else {
                $html .= "<p>This section covers {$heading} in detail with practical examples and actionable insights.</p>\n\n";
            }

            // Inject a stat or quote after the second section for richness
            if ($index === 1 && ! empty($stats)) {
                $stat = $stats[0];
                $html .= "<blockquote><p>📊 {$stat}</p></blockquote>\n\n";
            }
            if ($index === 3 && ! empty($quotes)) {
                $quote = $quotes[0];
                $html .= "<blockquote><p>{$quote}</p></blockquote>\n\n";
            }
        }

        // ── Tools mentioned ───────────────────────────────────────────────────
        if (! empty($tools)) {
            $html .= "<h2>Recommended Tools</h2>\n<ul>\n";
            foreach ($tools as $tool) {
                $toolName = $tool['name'] ?? '';
                $toolUrl  = $tool['url'] ?? '#';
                $html .= "  <li><a href=\"{$toolUrl}\" target=\"_blank\" rel=\"noopener noreferrer\">{$toolName}</a></li>\n";
            }
            $html .= "</ul>\n\n";
        }

        // ── Key Statistics ────────────────────────────────────────────────────
        if (! empty($stats)) {
            $html .= "<h2>Key Statistics</h2>\n<ul>\n";
            foreach ($stats as $stat) {
                $html .= "  <li>{$stat}</li>\n";
            }
            $html .= "</ul>\n\n";
        }

        // ── FAQ section ───────────────────────────────────────────────────────
        if (isset($cs['faq_section']) && filled($cs['faq_section'])) {
            $html .= "<h2>Frequently Asked Questions</h2>\n";
            // Try to parse Q&A pairs from the faq_section string
            $faqText = $cs['faq_section'];
            preg_match_all('/Q\d+:\s*([^?]+\?)\s*(?:A\d+:)?\s*([^Q]+?)(?=Q\d+:|$)/s', $faqText, $matches, PREG_SET_ORDER);
            if (! empty($matches)) {
                foreach ($matches as $match) {
                    $q = trim($match[1] ?? '');
                    $a = trim($match[2] ?? '');
                    if ($q) {
                        $html .= "<h3>{$q}</h3>\n";
                        if ($a) {
                            $html .= "<p>{$a}</p>\n\n";
                        } else {
                            $html .= "<p>This is a common question we cover in depth throughout the article above.</p>\n\n";
                        }
                    }
                }
            } else {
                // Fallback — just add the raw FAQ text as paragraph
                $html .= "<p>{$faqText}</p>\n\n";
            }
        }

        // ── Conclusion & CTA ──────────────────────────────────────────────────
        if (isset($cs['conclusion'])) {
            $html .= "<h2>Conclusion</h2>\n";
            $html .= "<p>{$cs['conclusion']}</p>\n\n";
        }

        if (! empty($cta['primary_cta'])) {
            $html .= "<p><strong>👉 {$cta['primary_cta']}</strong></p>\n\n";
        }
        if (! empty($cta['secondary_cta'])) {
            $html .= "<p>{$cta['secondary_cta']}</p>\n\n";
        }

        return trim($html);
    }
}
