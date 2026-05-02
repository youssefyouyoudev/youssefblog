<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Database\Seeders\DatabaseSeeder;

test('public launch pages render successfully', function () {
    $this->seed(DatabaseSeeder::class);

    $post = Post::latestPublished()->firstOrFail();
    $category = Category::firstOrFail();
    $tag = Tag::firstOrFail();

    foreach ([
        route('home'),
        route('posts.index'),
        route('posts.show', $post),
        route('categories.show', $category),
        route('tags.show', $tag),
        route('tools.index'),
        route('money.index'),
        route('money.show', config('money_pages.0.slug')),
        route('services'),
        route('services.alias'),
        route('about'),
        route('contact'),
        route('privacy'),
        route('terms'),
        route('editorial-policy'),
        route('affiliate-disclosure'),
        route('sitemap'),
        route('robots'),
        route('feed'),
        route('login'),
    ] as $url) {
        $this->get($url)->assertSuccessful();
    }
});
