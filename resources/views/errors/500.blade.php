<x-layouts.public :seo="['title' => 'Server Error | Youssef Blog', 'description' => 'Something went wrong on Youssef Blog.', 'noindex' => true]">
    <section class="mx-auto grid min-h-[60vh] max-w-3xl place-items-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <div>
            <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="mx-auto h-28 w-auto rounded-xl object-contain" width="280" height="112">
            <p class="mt-8 text-sm font-black uppercase text-emerald-600">500</p>
            <h1 class="mt-3 text-4xl font-black">Something went wrong.</h1>
            <p class="mt-4 text-slate-600">The site hit a temporary issue. You can return home and continue reading.</p>
            <a href="{{ route('home') }}" class="premium-button mt-8 bg-black text-white">Back Home</a>
        </div>
    </section>
</x-layouts.public>
