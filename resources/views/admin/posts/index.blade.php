<x-layouts.admin title="Posts">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-3xl font-black">Posts</h1>
        <a href="{{ route('admin.posts.create') }}" class="rounded-lg bg-black px-4 py-3 text-sm font-black text-white">Create Post</a>
    </div>
    <div class="mt-6 overflow-hidden rounded-lg border border-black/10 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100 text-xs uppercase text-slate-500">
                <tr><th class="p-4">Title</th><th class="p-4">Category</th><th class="p-4">Status</th><th class="p-4">Published</th><th class="p-4"></th></tr>
            </thead>
            <tbody class="divide-y divide-black/10">
                @foreach ($posts as $post)
                    <tr>
                        <td class="p-4 font-bold">{{ $post->title }}</td>
                        <td class="p-4">{{ $post->category->name }}</td>
                        <td class="p-4">{{ ucfirst($post->status) }}</td>
                        <td class="p-4">{{ $post->published_at?->format('M d, Y') ?? 'Not set' }}</td>
                        <td class="p-4">
                            <div class="flex justify-end gap-3">
                                <a class="font-black text-emerald-700" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                                    @csrf @method('DELETE')
                                    <button class="font-black text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
</x-layouts.admin>
