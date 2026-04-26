<x-layouts.public :seo="$seo">
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-emerald-600">Category</p>
            <h1 class="mt-3 text-4xl font-black sm:text-5xl">{{ $category->name }} Guides</h1>
            <p class="mt-4 max-w-3xl text-lg leading-8 text-slate-600">{{ $category->description }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-black">Latest in {{ $category->name }}</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ($featuredPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
        <x-ad-slot-middle label="Category ad placeholder" class="mt-8" />
    </section>

    <section class="border-y border-black/10 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black">Related Tags</h2>
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($relatedTags as $tag)
                    <a href="{{ route('tags.show', $tag) }}" class="rounded-lg border border-black/10 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-emerald-500 hover:text-emerald-700">#{{ $tag->name }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-black">All {{ $category->name }} Posts</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.public>
