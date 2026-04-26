<x-layouts.admin title="Edit Category">
    <h1 class="text-3xl font-black">Edit Category</h1>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-6 max-w-xl rounded-lg border border-black/10 bg-white p-5">
        @csrf @method('PUT')
        @include('admin.categories.form', ['button' => 'Update Category'])
    </form>
</x-layouts.admin>
