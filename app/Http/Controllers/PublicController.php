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
                'For Business Owners',
                'Website, CRM, dashboard, and automation guides for clearer operations and better client inquiries.',
                $this->homeGuidePosts([
                    'How Much Does a Professional Website Cost in 2026?',
                    'Why Your Business Should Stop Managing Everything in Excel',
                    'What a Custom CRM Can Do for a Small Business',
                ]),
            ],
            [
                'For Founders',
                'SaaS MVP, tech stack, validation, and launch planning for software products.',
                $this->homeGuidePosts([
                    'How to Build a SaaS MVP Without Wasting Your Budget',
                    'Common Mistakes Founders Make When Building Their First MVP',
                    'How to Choose the Right Tech Stack for a Business Web App',
                ]),
            ],
            [
                'For Web Project Planning',
                'Practical guidance for choosing, scoping, redesigning, and launching useful web systems.',
                $this->homeGuidePosts([
                    'Laravel vs WordPress for Business Websites: Which One Should You Choose?',
                    'The Difference Between a Website and a Web Application',
                    'Website Redesign Checklist for Businesses That Want More Clients',
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
        $popularPosts = Post::with('category', 'tags', 'user')
            ->latestPublished()
            ->orderByDesc('views')
            ->take(4)
            ->get();

        return view('public.home', [
            'featuredPosts' => $featuredPosts,
            'guideGroups' => $guideGroups,
            'latestPosts' => $latestPosts,
            'popularPosts' => $popularPosts,
            'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->get()
                ->filter(fn (Category $category) => $category->posts_count > 0)
                ->values(),
            'seo' => [
                'title' => 'Youssef Blog | Practical Laravel, AI, Finance & Digital Business Guides',
                'description' => 'Practical guides about Laravel apps, SaaS MVPs, dashboards, CRM systems, automation, AI tools, and business websites by Youssef Youyou.',
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
        $seo = app(SeoService::class);
        $hasSearch = filled(request('q'));

        return view('public.posts.index', [
            'posts' => Post::with('category', 'tags', 'user')
                ->latestPublished()
                ->when(request('q'), fn ($query, $search) => $query->where(fn ($searchQuery) => $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%')))
                ->paginate(9),
            'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->get(),
            'seo' => [
                'title' => 'Latest Articles | Youssef Blog',
                'description' => 'Browse practical guides about Laravel apps, SaaS MVPs, dashboards, CRM systems, automation, AI tools, and business websites by Youssef Youyou.',
                'canonical' => $hasSearch ? route('posts.index') : $seo->absoluteUrl(request()->fullUrl()),
                'robots' => $hasSearch ? 'noindex, follow' : 'index, follow, max-image-preview:large',
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
        $seoService = app(SeoService::class);

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
                'title' => $post->meta_title ?: $post->seo_title ?: $post->title.' | Youssef Blog',
                'description' => $seoService->descriptionFromPost($post),
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
        $category->loadCount(['posts' => fn ($query) => $query->published()]);
        $postsQuery = $category->posts()->with('category', 'tags', 'user')->latestPublished();
        $featuredPosts = (clone $postsQuery)->take(3)->get();
        $indexable = $category->posts_count >= 3 || filled($category->description);
        $seo = app(SeoService::class);

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
                'title' => $category->name.' Guides | Youssef Blog',
                'description' => $seo->categoryDescription($category->name, $category->meta_description ?: $category->description),
                'image' => asset('assets/brand/youssef-blog-og.png'),
                'canonical' => $seo->absoluteUrl(request()->fullUrl()),
                'robots' => $indexable ? 'index, follow, max-image-preview:large' : 'noindex, follow',
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
            'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->get(),
            'seo' => [
                'title' => '#'.$tag->name.' Articles | Youssef Blog',
                'description' => app(SeoService::class)->tagDescription($tag->name),
                'canonical' => app(SeoService::class)->absoluteUrl(request()->fullUrl()),
                'robots' => 'noindex, follow',
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
            'about' => 'Learn about Youssef Youyou, the developer behind practical Laravel, SaaS, AI, and business guides for Moroccan digital builders.',
            'contact' => 'Contact Youssef Youyou for Laravel websites, SaaS platforms, dashboards, automation, AI workflows, and digital business systems.',
            'privacy-policy' => 'Read the Youssef Blog privacy policy covering cookies, analytics, advertising partners, affiliate links, and contact data.',
            'terms' => 'Review the terms for using Youssef Blog, including educational content disclaimers, acceptable use, and website limitations.',
            'editorial-policy' => 'Read the Youssef Blog editorial policy covering accuracy, updates, affiliate transparency, and practical content standards.',
            'affiliate-disclosure' => 'Understand how Youssef Blog handles affiliate links, sponsored labels, recommendations, and editorial independence.',
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
                'robots' => in_array($page, ['privacy-policy', 'terms'], true) ? 'noindex, follow' : 'index, follow, max-image-preview:large',
            ],
        ]);
    }

    public function author(): View
    {
        return view('public.pages.author', [
            'latestPosts' => Post::with('category', 'tags', 'user')->latestPublished()->take(6)->get(),
            'seo' => [
                'title' => 'Youssef Youyou - Author & Full-Stack Developer',
                'description' => 'About Youssef Youyou, a full-stack Laravel developer writing practical guides about web development, SaaS, AI, automation, and digital business.',
                'canonical' => route('author.youssef'),
                'image' => asset('assets/brand/youssef-blog-og.png'),
                'breadcrumbs' => [
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => 'Youssef Youyou', 'url' => route('author.youssef')],
                ],
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
                'categories' => Category::withCount(['posts' => fn ($query) => $query->published()])
                    ->orderBy('name')
                    ->get()
                    ->filter(fn (Category $category) => $category->posts_count > 0)
                    ->values(),
            ])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        return response()->view('public.robots')->header('Content-Type', 'text/plain');
    }

    public function ads(): Response
    {
        return response('google.com, pub-1914940263140841, DIRECT, f08c47fec0942fa0'.PHP_EOL)
            ->header('Content-Type', 'text/plain');
    }

    public function feed(): Response
    {
        return response()
            ->view('public.feed', ['posts' => Post::with('category', 'tags', 'user')->latestPublished()->take(20)->get()])
            ->header('Content-Type', 'application/rss+xml');
    }
}
