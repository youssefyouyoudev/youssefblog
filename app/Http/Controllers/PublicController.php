<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Tool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'featuredPosts' => Post::with('category')->latestPublished()->where('is_featured', true)->take(3)->get(),
            'latestPosts' => Post::with('category', 'tags')->latestPublished()->take(6)->get(),
            'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])->orderBy('name')->get(),
            'tools' => Tool::where('is_featured', true)->orderBy('category')->take(4)->get(),
            'seo' => [
                'title' => 'Youssef Blog | Smart Finance, Tech & AI Guides',
                'description' => 'Actionable 2026 finance, technology, AI, Laravel, and online business guides for builders.',
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function posts(): View
    {
        return view('public.posts.index', [
            'posts' => Post::with('category', 'tags')->latestPublished()->paginate(9),
            'seo' => [
                'title' => 'Latest Posts | Youssef Blog',
                'description' => 'Browse the latest finance, tech, AI, Laravel, and online business articles from Youssef Blog.',
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless(in_array($post->status, ['published', 'scheduled'], true) && $post->published_at?->lte(now()), 404);

        $post->load('category', 'tags', 'user');
        $post->increment('views');

        $relatedPosts = Post::with('category')
            ->latestPublished()
            ->whereKeyNot($post->id)
            ->where('category_id', $post->category_id)
            ->take(3)
            ->get();

        $previousPost = Post::published()->where('published_at', '<', $post->published_at)->latest('published_at')->first();
        $nextPost = Post::published()->where('published_at', '>', $post->published_at)->oldest('published_at')->first();

        return view('public.posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'internalLinks' => Post::with('category')
                ->latestPublished()
                ->whereKeyNot($post->id)
                ->where(function ($query) use ($post): void {
                    $query->where('category_id', $post->category_id)
                        ->orWhereHas('tags', fn ($tagQuery) => $tagQuery->whereIn('tags.id', $post->tags->pluck('id')));
                })
                ->take(5)
                ->get(),
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'seo' => [
                'title' => $post->seo_title ?: $post->title.' | Youssef Blog',
                'description' => $post->meta_description ?: $post->excerpt,
                'canonical' => $post->canonical_url ?: route('posts.show', $post),
                'image' => $post->og_image ?: $post->featured_image ?: asset('assets/brand/youssef-blog-og.png'),
                'type' => 'article',
                'keywords' => $post->keywordList(),
            ],
        ]);
    }

    public function category(Category $category): View
    {
        $postsQuery = $category->posts()->with('category', 'tags')->latestPublished();

        return view('public.posts.category', [
            'category' => $category,
            'featuredPosts' => (clone $postsQuery)->take(3)->get(),
            'posts' => $postsQuery->paginate(9),
            'relatedTags' => Tag::whereHas('posts', fn ($query) => $query->where('category_id', $category->id)->published())
                ->withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->take(10)
                ->get(),
            'seo' => [
                'title' => ($category->seo_title ?: $category->name).' | Youssef Blog',
                'description' => $category->meta_description ?: "Read {$category->name} guides on Youssef Blog.",
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function tag(Tag $tag): View
    {
        return view('public.posts.index', [
            'heading' => '#'.$tag->name,
            'posts' => $tag->posts()->with('category', 'tags')->latestPublished()->paginate(9),
            'seo' => [
                'title' => '#'.$tag->name.' | Youssef Blog',
                'description' => "Read practical {$tag->name} articles from Youssef Blog.",
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function page(string $page): View
    {
        abort_unless(in_array($page, ['about', 'contact', 'privacy-policy', 'terms', 'editorial-policy', 'affiliate-disclosure'], true), 404);

        $titles = [
            'about' => 'About Youssef Blog',
            'contact' => 'Contact',
            'privacy-policy' => 'Privacy Policy',
            'terms' => 'Terms',
            'editorial-policy' => 'Editorial Policy',
            'affiliate-disclosure' => 'Affiliate Disclosure',
        ];
        $descriptions = [
            'about' => 'Learn about Youssef Blog, a finance, tech, AI, Laravel, and online business media site by Youssef Youyou.',
            'contact' => 'Contact Youssef Blog for partnerships, corrections, sponsorship questions, and business inquiries.',
            'privacy-policy' => 'Privacy Policy for Youssef Blog covering cookies, analytics, advertising partners, affiliate links, and contact data.',
            'terms' => 'Terms for using Youssef Blog, including educational content disclaimers and acceptable use.',
            'editorial-policy' => 'Editorial policy for Youssef Blog, including content standards, updates, affiliate transparency, and trust principles.',
            'affiliate-disclosure' => 'Affiliate disclosure for Youssef Blog explaining affiliate links, sponsored content labels, and editorial independence.',
        ];

        return view("public.pages.{$page}", [
            'seo' => [
                'title' => $titles[$page].' | Youssef Blog',
                'description' => $descriptions[$page],
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function contact(ContactRequest $request): RedirectResponse
    {
        return back()->with('status', 'Thanks. Your message has been received for review.');
    }

    public function tools(): View
    {
        return view('public.tools.index', [
            'toolsByCategory' => Tool::orderBy('category')->orderByDesc('is_featured')->orderBy('name')->get()->groupBy('category'),
            'seo' => [
                'title' => 'Recommended Tools | Youssef Blog',
                'description' => 'Recommended hosting, AI, developer, and finance tools for builders in 2026.',
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function sitemap(): Response
    {
        return response()
            ->view('public.sitemap', [
                'posts' => Post::latestPublished()->get(),
                'categories' => Category::orderBy('name')->get(),
                'tags' => Tag::orderBy('name')->get(),
            ])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        return response()->view('public.robots')->header('Content-Type', 'text/plain');
    }

    public function feed(): Response
    {
        return response()
            ->view('public.feed', ['posts' => Post::with('category')->latestPublished()->take(20)->get()])
            ->header('Content-Type', 'application/rss+xml');
    }
}
