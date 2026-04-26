<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Models\PublishLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HandlePostPublished
{
    public function handle(PostPublished $event): void
    {
        $post = $event->post;

        Cache::flush();

        PublishLog::create([
            'post_id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'published_at' => $post->published_at ?: now(),
            'meta' => ['url' => route('posts.show', $post)],
        ]);

        if (filled(config('services.indexnow.key'))) {
            Http::timeout(5)->retry(1, 250)->post('https://api.indexnow.org/indexnow', [
                'host' => parse_url(config('app.url'), PHP_URL_HOST),
                'key' => config('services.indexnow.key'),
                'keyLocation' => config('services.indexnow.key_location'),
                'urlList' => [route('posts.show', $post)],
            ]);
        }

        Log::info('Post published.', ['post_id' => $post->id, 'slug' => $post->slug]);
    }
}
