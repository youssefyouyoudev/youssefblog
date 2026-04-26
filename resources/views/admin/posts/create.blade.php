<x-layouts.admin title="Create Post">
    <h1 class="text-3xl font-black">Create Post</h1>
    <form method="POST" action="{{ route('admin.posts.store') }}" class="mt-6">
        @csrf
        @include('admin.posts.form')
    </form>
</x-layouts.admin>
