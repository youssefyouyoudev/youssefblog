<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'postCount' => Post::count(),
            'publishedCount' => Post::published()->count(),
            'draftCount' => Post::where('status', 'draft')->count(),
            'categoryCount' => Category::count(),
            'tagCount' => Tag::count(),
            'recentPosts' => Post::with('category')->latest()->take(5)->get(),
        ]);
    }
}
