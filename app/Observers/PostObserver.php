<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function saved(Post $post): void
    {
        $this->bust();
    }

    public function deleted(Post $post): void
    {
        $this->bust();
    }

    private function bust(): void
    {
        Cache::flush();
    }
}
