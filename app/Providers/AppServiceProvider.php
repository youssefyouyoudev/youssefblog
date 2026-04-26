<?php

namespace App\Providers;

use App\Events\PostPublished;
use App\Listeners\HandlePostPublished;
use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Post::observe(PostObserver::class);
        Event::listen(PostPublished::class, HandlePostPublished::class);
    }
}
