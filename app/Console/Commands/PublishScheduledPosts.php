<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish scheduled posts whose publish date has passed.';

    public function handle(): int
    {
        $count = Post::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published']);

        Log::info('Scheduled posts published.', ['count' => $count]);
        $this->info("Published {$count} scheduled posts.");

        return self::SUCCESS;
    }
}
