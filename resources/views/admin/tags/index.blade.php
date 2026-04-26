<x-layouts.admin title="Tags">
    <h1 class="text-3xl font-black">Tags</h1>
    <div class="mt-6 grid gap-6 lg:grid-cols-[380px_1fr]">
        <form method="POST" action="{{ route('admin.tags.store') }}" class="rounded-lg border border-black/10 bg-white p-5">
            @csrf
            @include('admin.tags.form', ['button' => 'Create Tag'])
        </form>
        <div class="overflow-hidden rounded-lg border border-black/10 bg-white">
            @foreach ($tags as $tag)
                <div class="flex items-center justify-between gap-4 border-b border-black/10 p-4 last:border-b-0">
                    <div><p class="font-black">{{ $tag->name }}</p><p class="text-sm text-slate-500">{{ $tag->posts_count }} posts</p></div>
                    <div class="flex gap-3 text-sm">
                        <a class="font-black text-emerald-700" href="{{ route('admin.tags.edit', $tag) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" onsubmit="return confirm('Delete this tag?')">
                            @csrf @method('DELETE')
                            <button class="font-black text-red-600">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="mt-6">{{ $tags->links() }}</div>
</x-layouts.admin>
