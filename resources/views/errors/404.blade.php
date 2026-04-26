<x-layouts.public :seo="['title' => 'Page Not Found | Youssef Blog', 'description' => 'The page you are looking for was not found.', 'image' => asset('assets/brand/youssef-blog-og.png')]">
    <section class="mx-auto grid min-h-[60vh] max-w-3xl place-items-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <div>
            <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="mx-auto h-28 w-auto rounded-xl object-contain" width="280" height="112">
            <p class="mt-8 text-sm font-black uppercase text-emerald-600">404</p>
            <h1 class="mt-3 text-4xl font-black">This page is off the map.</h1>
            <p class="mt-4 text-slate-600">Try the latest guides or return home.</p>
            <div class="mt-8 flex justify-center gap-3">
                <a href="{{ route('home') }}" class="rounded-lg bg-black px-5 py-3 text-sm font-black text-white">Home</a>
                <a href="{{ route('posts.index') }}" class="rounded-lg border border-black/10 bg-white px-5 py-3 text-sm font-black text-black">Latest Posts</a>
            </div>
        </div>
    </section>
</x-layouts.public>
