<x-layouts.admin title="Dashboard">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">Dashboard</h1>
            <p class="mt-1 text-slate-600">Publishing overview for Youssef Blog.</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="rounded-lg bg-black px-4 py-3 text-sm font-black text-white">New Post</a>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        @foreach ([['Total Posts', $postCount], ['Published', $publishedCount], ['Drafts', $draftCount], ['Scheduled', $scheduledCount], ['Categories', $categoryCount], ['Tags', $tagCount]] as [$label, $value])
            <div class="rounded-lg border border-black/10 bg-white p-5">
                <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([['Views', number_format($totalViews)], ['Ad CTR', $ctr.'%'], ['Ad Clicks', number_format($adClicks)], ['Affiliate Clicks', number_format($affiliateClicks)], ['Est. RPM Model', '$'.number_format($estimatedRpm, 2)]] as [$label, $value])
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-50 p-5">
                <p class="text-sm font-bold text-emerald-700">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-4 rounded-lg border border-black/10 bg-white p-5">
        <p class="text-sm font-bold text-slate-500">Estimated display ad revenue model</p>
        <p class="mt-2 text-3xl font-black">${{ number_format($estimatedRevenue, 2) }}</p>
        <p class="mt-2 text-xs leading-5 text-slate-500">Planning estimate only. Real RPM depends on niche, countries, viewability, policy compliance, and advertiser demand.</p>
    </div>
    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-lg border border-black/10 bg-white">
            <div class="border-b border-black/10 p-5"><h2 class="font-black">Top Posts</h2></div>
            <div class="divide-y divide-black/10">
                @foreach ($topPosts as $post)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <div>
                            <p class="font-bold">{{ $post->title }}</p>
                            <p class="text-sm text-slate-500">{{ $post->category?->name }} / {{ number_format($post->views) }} views / {{ number_format($post->affiliate_clicks) }} affiliate clicks</p>
                        </div>
                        <a class="text-sm font-black text-emerald-700" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="rounded-lg border border-black/10 bg-white">
            <div class="border-b border-black/10 p-5"><h2 class="font-black">Top Categories</h2></div>
            <div class="divide-y divide-black/10">
                @foreach ($topCategories as $category)
                    <div class="flex items-center justify-between gap-4 p-5">
                        <p class="font-bold">{{ $category->name }}</p>
                        <p class="text-sm font-black text-slate-500">{{ number_format($category->total_views ?? 0) }} views</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
    <section class="mt-8 rounded-lg border border-black/10 bg-white">
        <div class="border-b border-black/10 p-5"><h2 class="font-black">Recent Posts</h2></div>
        <div class="divide-y divide-black/10">
            @foreach ($recentPosts as $post)
                <div class="flex items-center justify-between gap-4 p-5">
                    <div>
                        <p class="font-bold">{{ $post->title }}</p>
                        <p class="text-sm text-slate-500">{{ $post->category->name }} • {{ ucfirst($post->status) }}</p>
                    </div>
                    <a class="text-sm font-black text-emerald-700" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                </div>
            @endforeach
        </div>
    </section>
    <section class="mt-8 rounded-lg border border-black/10 bg-white p-5">
        <h2 class="font-black">Quick Actions</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('admin.posts.create') }}" class="rounded-lg bg-black px-4 py-2 text-sm font-black text-white">Create Post</a>
            <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-black/10 px-4 py-2 text-sm font-black">Manage Categories</a>
            <a href="{{ route('admin.tags.index') }}" class="rounded-lg border border-black/10 px-4 py-2 text-sm font-black">Manage Tags</a>
        </div>
    </section>
</x-layouts.admin>
