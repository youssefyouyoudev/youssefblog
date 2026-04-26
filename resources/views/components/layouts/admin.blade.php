<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Admin' }} | Youssef Blog</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/brand/youssef-blog-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans">
    <div class="min-h-screen lg:flex">
        <aside class="border-r border-black/10 bg-black text-white lg:w-72">
            <div class="flex items-center justify-between px-5 py-5 lg:block">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="h-12 w-auto rounded-md object-contain" width="96" height="48">
                    <span class="text-xl font-black text-brand">Admin</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">@csrf<button class="text-sm font-bold">Logout</button></form>
            </div>
            <nav class="grid gap-1 px-3 pb-5 text-sm font-semibold">
                <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="{{ route('admin.posts.index') }}">Posts</a>
                <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="{{ route('admin.categories.index') }}">Categories</a>
                <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="{{ route('admin.tags.index') }}">Tags</a>
                <a class="rounded-lg px-3 py-2 hover:bg-white/10" href="{{ route('home') }}">View Site</a>
                <form method="POST" action="{{ route('logout') }}" class="hidden lg:block">@csrf<button class="w-full rounded-lg px-3 py-2 text-left hover:bg-white/10">Logout</button></form>
            </nav>
        </aside>
        <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
