<?php

use App\Models\Post;
use App\Services\ProductionPostContent;
use Database\Seeders\DatabaseSeeder;

test('production seeded posts meet editorial quality requirements', function () {
    $this->seed(DatabaseSeeder::class);

    $slugs = collect(array_keys(require database_path('seeders/data/production-post-content.php')));
    $posts = Post::whereIn('slug', $slugs)->get();

    expect($posts)->toHaveCount(20);
    expect($posts->pluck('slug')->unique())->toHaveCount(20);

    foreach ($posts as $post) {
        expect(ProductionPostContent::wordCount($post->content))->toBeGreaterThanOrEqual(3000);
        expect($post->meta_title)->not->toBeEmpty();
        expect($post->meta_description)->not->toBeEmpty();
        expect($post->excerpt)->not->toBeEmpty();
        expect($post->featured_image)->not->toBeEmpty();
        expect($post->published_at)->not->toBeNull();
        expect($post->published_at->isFuture())->toBeFalse();

        foreach (ProductionPostContent::bannedPhrases() as $phrase) {
            expect(str_contains(mb_strtolower($post->content), mb_strtolower($phrase)))->toBeFalse();
        }
    }
});
