@props(['posts'])

@if ($posts->isNotEmpty())
    <section class="mt-12">
        <p class="text-xs font-black uppercase tracking-[0.2em] text-[var(--accent)]">Related guides</p>
        <h2 class="mt-3 text-2xl font-black text-[var(--text)]">Keep reading</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach ($posts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>
    </section>
@endif
