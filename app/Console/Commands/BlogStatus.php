<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class BlogStatus extends Command
{
    protected $signature = 'blog:status';

    protected $description = 'Show publishing health for Youssef Blog.';

    public function handle(): int
    {
        $this->info('Youssef Blog Status');
        $this->line('Total posts: '.Post::count());
        $this->line('Published posts: '.Post::published()->count());
        $this->line('Scheduled posts: '.Post::scheduled()->count());

        $this->table(
            ['Title', 'Scheduled Time'],
            Post::scheduled()->oldest('published_at')->take(3)->get(['title', 'published_at'])->map(fn (Post $post): array => [
                $post->title,
                $post->published_at?->format('Y-m-d H:i:s'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
