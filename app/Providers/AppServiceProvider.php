<?php

namespace App\Providers;

use App\Events\PostPublished;
use App\Listeners\HandlePostPublished;
use App\Models\Category;
use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }

        Post::observe(PostObserver::class);
        Event::listen(PostPublished::class, HandlePostPublished::class);

        View::composer('components.public.footer', function ($view): void {
            $view->with('footerCategories', Category::withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->take(6)
                ->get());
        });
    }
}
