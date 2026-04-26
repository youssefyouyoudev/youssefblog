@props(['posts'])

@if ($posts->isNotEmpty())
    <section class="mt-12">
        <h2 class="text-2xl font-black">Related Posts</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>
@endif
