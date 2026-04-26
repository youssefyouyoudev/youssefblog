<x-layouts.public :seo="$seo">
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-emerald-600">Youssef Blog</p>
            <h1 class="mt-3 text-4xl font-black sm:text-5xl">{{ $heading ?? 'Latest Posts' }}</h1>
            @isset($description)
                <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $description }}</p>
            @endisset
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <x-post-card :post="$post" />
            @empty
                <p class="rounded-lg border border-black/10 bg-white p-6 text-slate-600">No published posts yet.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    </section>
</x-layouts.public>
