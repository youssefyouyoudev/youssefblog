<x-layouts.admin title="Edit Post">
    <h1 class="text-3xl font-black">Edit Post</h1>
    <form method="POST" action="{{ route('admin.posts.update', $post) }}" class="mt-6">
        @csrf @method('PUT')
        @include('admin.posts.form')
    </form>
</x-layouts.admin>
