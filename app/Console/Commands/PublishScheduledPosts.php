<?php

namespace App\Console\Commands;

use App\Events\PostPublished;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish scheduled posts whose publish date has passed.';

    public function handle(): int
    {
        $posts = Post::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        $posts->each(function (Post $post): void {
            $post->forceFill([
                'status' => 'published',
                'last_updated_at' => now(),
            ])->save();

            PostPublished::dispatch($post);
        });

        $count = $posts->count();

        Log::info('Scheduled posts published.', ['count' => $count]);
        $this->info("Published {$count} scheduled posts.");

        return self::SUCCESS;
    }
}
