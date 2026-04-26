<x-layouts.public :seo="$seo">
    <section class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-16">
        <form method="POST" action="{{ route('login.store') }}" class="w-full rounded-lg border border-black/10 bg-white p-6 shadow-xl">
            @csrf
            <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Blog - Finance Tech AI" class="mx-auto mb-6 h-24 w-auto rounded-lg object-contain" width="220" height="96">
            <h1 class="text-2xl font-black">Admin Login</h1>
            <label class="mt-6 block text-sm font-bold">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full border border-black/10 px-3 py-3" required autofocus>
            @error('email')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            <label class="mt-4 block text-sm font-bold">Password</label>
            <input name="password" type="password" class="mt-2 w-full border border-black/10 px-3 py-3" required>
            @error('password')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            <label class="mt-4 flex items-center gap-2 text-sm font-semibold text-slate-600">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>
            <button class="mt-6 w-full rounded-lg bg-black px-4 py-3 font-black text-white">Login</button>
        </form>
    </section>
</x-layouts.public>
