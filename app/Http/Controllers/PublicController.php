<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Tool;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $featuredPosts = Post::with('category', 'tags', 'user')
            ->latestPublished()
            ->where('is_featured', true)
            ->take(3)
            ->get();

        if ($featuredPosts->isEmpty()) {
            $featuredPosts = Post::with('category', 'tags', 'user')
                ->latestPublished()
                ->take(3)
                ->get();
        }

        $guideGroups = [
            [
                'For Moroccan Freelancers',
                'Money, budgeting, and online income guides for realistic freelance growth.',
                $this->homeGuidePosts([
                    'Online Income Ideas That Fit Beginners in Morocco',
                    'Budgeting for Freelancers in Morocco in 2026',
                    'How Moroccan Freelancers Can Make Money Online in 2026',
                ]),
            ],
            [
                'For Developers',
                'Finance and business systems for developers building useful side projects.',
                $this->homeGuidePosts([
                    'Personal Finance System for Developers With Side Projects',
                    'Beginner Investing Education: What to Learn Before You Invest',
                    'Avoiding Debt While Building an Online Business',
                    'Side Hustle Budgeting: How to Reinvest Without Overspending',
                ]),
            ],
            [
                'For Business Builders',
                'Practical technology, AI, and SaaS guidance for stronger digital operations.',
                $this->homeGuidePosts([
                    'Laravel SEO Guide 2026 for Blade Blogs',
                    'AI Automation Ideas for Small Online Businesses',
                    'SaaS Ideas Morocco 2026: Practical Local Niches',
                ]),
            ],
        ];
        $guidePostIds = collect($guideGroups)
            ->flatMap(fn (array $group) => $group[2]->modelKeys())
            ->unique()
            ->values()
            ->all();
        $latestPosts = Post::with('category', 'tags', 'user')
            ->latestPublished()
            ->when($guidePostIds, fn ($query) => $query->whereKeyNot($guidePostIds))
            ->when($featuredPosts->isNotEmpty(), fn ($query) => $query->whereKeyNot($featuredPosts->modelKeys()))
            ->take(6)
            ->get();

        return view('public.home', [
            'featuredPosts' => $featuredPosts,
            'guideGroups' => $guideGroups,
            'latestPosts' => $latestPosts,
            'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->get(),
            'seo' => [
                'title' => 'Practical Laravel, SaaS, AI & Business Guides | Youssef Youyou Blog',
                'description' => 'Real-world Laravel, SaaS, AI, finance systems, and digital business guides by Youssef Youyou for developers, freelancers, and Moroccan SMEs.',
                'canonical' => route('home'),
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    private function homeGuidePosts(array $titles)
    {
        $posts = Post::with('category', 'tags', 'user')
            ->latestPublished()
            ->whereIn('title', $titles)
            ->get()
            ->sortBy(fn (Post $post) => array_search($post->title, $titles, true))
            ->values();

        if ($posts->count() >= 3) {
            return $posts;
        }

        return $posts
            ->merge(Post::with('category', 'tags', 'user')
                ->latestPublished()
                ->when($posts->isNotEmpty(), fn ($query) => $query->whereKeyNot($posts->modelKeys()))
                ->take(3 - $posts->count())
                ->get())
            ->values();
    }

    public function posts(): View
    {
        return view('public.posts.index', [
            'posts' => Post::with('category', 'tags', 'user')
                ->latestPublished()
                ->when(request('q'), fn ($query, $search) => $query->where(fn ($searchQuery) => $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%')))
                ->paginate(9),
            'seo' => [
                'title' => 'Latest Posts | Youssef Blog',
                'description' => 'Browse the latest finance, tech, AI, Laravel, and online business articles from Youssef Blog.',
                'canonical' => route('posts.index'),
                'image' => asset('assets/brand/youssef-blog-og.png'),
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => 'Articles', 'url' => route('posts.index')],
                ],
            ],
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless(in_array($post->status, ['published', 'scheduled'], true) && $post->published_at?->lte(now()), 404);

        $post->load('category', 'tags', 'user');

        $relatedPosts = Post::with('category', 'tags', 'user')
            ->latestPublished()
            ->whereKeyNot($post->id)
            ->where('category_id', $post->category_id)
            ->take(3)
            ->get();

        $previousPost = Post::published()->where('category_id', $post->category_id)->where('published_at', '<', $post->published_at)->latest('published_at')->first();
        $nextPost = Post::published()->where('category_id', $post->category_id)->where('published_at', '>', $post->published_at)->oldest('published_at')->first();

        return view('public.posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'internalLinks' => Post::with('category', 'tags', 'user')
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
                'published_time' => $post->published_at?->toIso8601String(),
                'modified_time' => ($post->last_updated_at ?: $post->updated_at)?->toIso8601String(),
                'author' => $post->user?->name ?: config('brand.name'),
                'schemas' => app(SeoService::class)->postSchemas($post),
            ],
        ]);
    }

    public function category(Category $category): View
    {
        $postsQuery = $category->posts()->with('category', 'tags', 'user')->latestPublished();
        $featuredPosts = (clone $postsQuery)->take(3)->get();

        return view('public.posts.category', [
            'category' => $category,
            'featuredPosts' => $featuredPosts,
            'posts' => $postsQuery
                ->when($featuredPosts->isNotEmpty(), fn ($query) => $query->whereKeyNot($featuredPosts->modelKeys()))
                ->paginate(9),
            'relatedTags' => Tag::whereHas('posts', fn ($query) => $query->where('category_id', $category->id)->published())
                ->withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->take(10)
                ->get(),
            'seo' => [
                'title' => ($category->seo_title ?: $category->name).' | Youssef Blog',
                'description' => $category->meta_description ?: "Read {$category->name} guides on Youssef Blog.",
                'image' => asset('assets/brand/youssef-blog-og.png'),
                'canonical' => route('categories.show', $category),
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => $category->name, 'url' => route('categories.show', $category)],
                ],
            ],
        ]);
    }

    public function tag(Tag $tag): View
    {
        return view('public.posts.index', [
            'heading' => '#'.$tag->name,
            'posts' => $tag->posts()->with('category', 'tags', 'user')->latestPublished()->paginate(9),
            'seo' => [
                'title' => '#'.$tag->name.' | Youssef Blog',
                'description' => "Read practical {$tag->name} articles from Youssef Blog.",
                'canonical' => route('tags.show', $tag),
                'image' => asset('assets/brand/youssef-blog-og.png'),
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => '#'.$tag->name, 'url' => route('tags.show', $tag)],
                ],
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
            'about' => 'Learn about Youssef Youyou, Senior Full-Stack Developer in Morocco, and the Youssef Blog media arm for finance, tech, AI, Laravel, SaaS, and digital business.',
            'contact' => 'Contact Youssef Youyou for premium websites, SaaS platforms, dashboards, CRM/ERP systems, APIs, automation, and Laravel development.',
            'privacy-policy' => 'Privacy Policy for Youssef Blog covering cookies, analytics, advertising partners, affiliate links, and contact data.',
            'terms' => 'Terms for using Youssef Blog, including educational content disclaimers and acceptable use.',
            'editorial-policy' => 'Editorial policy for Youssef Blog, including content standards, updates, affiliate transparency, and trust principles.',
            'affiliate-disclosure' => 'Affiliate disclosure for Youssef Blog explaining affiliate links, sponsored content labels, and editorial independence.',
        ];

        return view("public.pages.{$page}", [
            'seo' => [
                'title' => $titles[$page].' | Youssef Blog',
                'description' => $descriptions[$page],
                'canonical' => route(match ($page) {
                    'about' => 'about',
                    'contact' => 'contact',
                    'privacy-policy' => 'privacy',
                    'terms' => 'terms',
                    'editorial-policy' => 'editorial-policy',
                    'affiliate-disclosure' => 'affiliate-disclosure',
                }),
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
                'canonical' => route('tools.index'),
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function moneyIndex(): View
    {
        return view('public.money.index', [
            'moneyPages' => collect(config('money_pages')),
            'seo' => [
                'title' => 'Best Tools, Hosting, Laptops & Finance Comparisons | Youssef Blog',
                'description' => 'Affiliate-ready comparison guides for hosting, Laravel VPS, AI tools, laptops, budget phones, banking, and side hustle tools.',
                'canonical' => route('money.index'),
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function moneyShow(string $slug): View
    {
        $page = collect(config('money_pages'))->firstWhere('slug', $slug);

        abort_unless($page, 404);

        return view('public.money.show', [
            'page' => $page,
            'relatedPages' => collect(config('money_pages'))->where('slug', '!=', $slug)->take(3),
            'seo' => [
                'title' => $page['title'].' | Youssef Blog',
                'description' => $page['excerpt'],
                'canonical' => route('money.show', $page['slug']),
                'image' => $page['image'] ?? asset('assets/brand/youssef-blog-og.png'),
                'type' => 'article',
                'keywords' => implode(', ', $page['keywords'] ?? []),
            ],
        ]);
    }

    public function services(): View
    {
        return view('public.services', [
            'seo' => [
                'title' => 'Work With Youssef Youyou | Websites, SaaS, Dashboards & Laravel',
                'description' => 'Hire Youssef Youyou for premium business websites, SaaS MVPs, dashboards, CRM/ERP systems, Laravel development, APIs, automation, and AI workflows.',
                'canonical' => route('services'),
                'image' => asset('assets/brand/youssef-blog-og.png'),
            ],
        ]);
    }

    public function sitemap(): Response
    {
        return response()
            ->view('public.sitemap', [
                'posts' => Post::with('category', 'tags', 'user')->latestPublished()->get(),
                'categories' => Category::orderBy('name')->get(),
                'tags' => Tag::orderBy('name')->get(),
                'servicePages' => [route('services')],
                'moneyPages' => collect(config('money_pages')),
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
            ->view('public.feed', ['posts' => Post::with('category', 'tags', 'user')->latestPublished()->take(20)->get()])
            ->header('Content-Type', 'application/rss+xml');
    }
}
