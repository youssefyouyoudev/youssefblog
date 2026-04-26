<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostViewLog;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalViews = Post::sum('views');
        $adClicks = Post::sum('ad_clicks');
        $affiliateClicks = Post::sum('affiliate_clicks');
        $estimatedRpm = 3.5;

        return view('admin.dashboard', [
            'postCount' => Post::count(),
            'publishedCount' => Post::published()->count(),
            'draftCount' => Post::where('status', 'draft')->count(),
            'scheduledCount' => Post::where('status', 'scheduled')->count(),
            'categoryCount' => Category::count(),
            'tagCount' => Tag::count(),
            'recentPosts' => Post::with('category')->latest()->take(5)->get(),
            'topPosts' => Post::with('category')->orderByDesc('views')->take(5)->get(),
            'topCategories' => Category::withSum('posts as total_views', 'views')->orderByDesc('total_views')->take(5)->get(),
            'publishedToday' => Post::published()->whereDate('published_at', today())->count(),
            'viewsToday' => PostViewLog::whereDate('viewed_at', today())->count(),
            'topPostsThisWeek' => Post::withCount(['viewLogs as week_views' => fn ($query) => $query->where('viewed_at', '>=', now()->startOfWeek())])
                ->orderByDesc('week_views')
                ->take(5)
                ->get(),
            'totalViews' => $totalViews,
            'adClicks' => $adClicks,
            'affiliateClicks' => $affiliateClicks,
            'estimatedRpm' => $estimatedRpm,
            'estimatedRevenue' => round(($totalViews / 1000) * $estimatedRpm, 2),
            'ctr' => $totalViews > 0 ? round(($adClicks / $totalViews) * 100, 2) : 0,
        ]);
    }
}
