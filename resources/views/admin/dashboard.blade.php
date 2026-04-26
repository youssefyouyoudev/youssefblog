<x-layouts.admin title="Dashboard">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black">Dashboard</h1>
            <p class="mt-1 text-slate-600">Publishing overview for Youssef Blog.</p>
        </div>
        <a href="{{ route('admin.posts.create') }}" class="rounded-lg bg-black px-4 py-3 text-sm font-black text-white">New Post</a>
    </div>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([['Total Posts', $postCount], ['Published', $publishedCount], ['Drafts', $draftCount], ['Categories', $categoryCount], ['Tags', $tagCount]] as [$label, $value])
            <div class="rounded-lg border border-black/10 bg-white p-5">
                <p class="text-sm font-bold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black">{{ $value }}</p>
            </div>
        @endforeach
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
</x-layouts.admin>
