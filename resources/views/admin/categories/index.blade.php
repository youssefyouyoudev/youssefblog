<x-layouts.admin title="Categories">
    <h1 class="text-3xl font-black">Categories</h1>
    <div class="mt-6 grid gap-6 lg:grid-cols-[380px_1fr]">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="rounded-lg border border-black/10 bg-white p-5">
            @csrf
            @include('admin.categories.form', ['button' => 'Create Category'])
        </form>
        <div class="overflow-hidden rounded-lg border border-black/10 bg-white">
            @foreach ($categories as $category)
                <div class="flex items-center justify-between gap-4 border-b border-black/10 p-4 last:border-b-0">
                    <div><p class="font-black">{{ $category->name }}</p><p class="text-sm text-slate-500">{{ $category->posts_count }} posts</p></div>
                    <div class="flex gap-3 text-sm">
                        <a class="font-black text-emerald-700" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="font-black text-red-600">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="mt-6">{{ $categories->links() }}</div>
</x-layouts.admin>
