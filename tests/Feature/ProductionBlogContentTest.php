<?php

use App\Models\Post;
use App\Services\ProductionPostContent;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductionScheduledPostsSeeder;

test('production and scheduled seeded posts meet editorial quality requirements', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(ProductionScheduledPostsSeeder::class);

    $productionSlugs = collect(array_keys(require database_path('seeders/data/production-post-content.php')));
    $scheduledSlugs = collect(require database_path('seeders/data/scheduled-posts.php'))->pluck('slug');
    $slugs = $productionSlugs->merge($scheduledSlugs);
    $posts = Post::whereIn('slug', $slugs)->get();

    expect($posts)->toHaveCount(50);
    expect($posts->pluck('slug')->unique())->toHaveCount(50);

    foreach ($posts as $post) {
        expect(ProductionPostContent::wordCount($post->content))->toBeGreaterThanOrEqual(3000);
        expect($post->meta_title)->not->toBeEmpty();
        expect($post->meta_description)->not->toBeEmpty();
        expect($post->excerpt)->not->toBeEmpty();
        expect($post->featured_image)->not->toBeEmpty();
        expect($post->published_at)->not->toBeNull();

        if ($post->status === 'scheduled') {
            expect($post->published_at->isFuture())->toBeTrue();
        } else {
            expect($post->published_at->isFuture())->toBeFalse();
        }

        foreach (ProductionPostContent::bannedPhrases() as $phrase) {
            expect(str_contains(mb_strtolower($post->content), mb_strtolower($phrase)))->toBeFalse();
        }
    }
});
