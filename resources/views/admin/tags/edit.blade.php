<x-layouts.admin title="Edit Tag">
    <h1 class="text-3xl font-black">Edit Tag</h1>
    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="mt-6 max-w-xl rounded-lg border border-black/10 bg-white p-5">
        @csrf @method('PUT')
        @include('admin.tags.form', ['button' => 'Update Tag'])
    </form>
</x-layouts.admin>
