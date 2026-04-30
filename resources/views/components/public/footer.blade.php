@php
    $brand = config('brand');
    $categories = \App\Models\Category::withCount(['posts' => fn ($query) => $query->published()])
        ->orderByDesc('posts_count')
        ->take(6)
        ->get();
    $socialLinks = collect($brand['social'] ?? [])->filter(fn ($url) => ! in_array(rtrim($url, '/'), ['https://github.com', 'https://linkedin.com'], true));
    $socialLabels = ['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'twitter' => 'X', 'youtube' => 'YouTube'];
@endphp

<footer class="border-t border-[var(--border)] bg-[var(--surface-2)]">
    <div class="safe-container grid gap-9 py-12 sm:grid-cols-2 lg:grid-cols-5 lg:py-14">
        <div class="sm:col-span-2">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('assets/brand/youssef-blog-logo.png') }}" alt="Youssef Youyou Blog" class="h-12 w-12 rounded-2xl object-cover ring-1 ring-[var(--border)]" width="48" height="48" loading="lazy">
                <span class="min-w-0">
                    <span class="block text-lg font-black text-[var(--text)]">Youssef Youyou Blog</span>
                    <span class="block text-sm font-bold text-[var(--muted)]">Practical guides for serious builders</span>
                </span>
            </a>
            <p class="mt-5 max-w-md text-sm leading-6 text-[var(--muted)]">{{ $brand['insights'] }} {{ $brand['positioning'] }}</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach (['Laravel', 'SaaS', 'AI Tools', 'Moroccan SMEs'] as $chip)
                    <span class="rounded-full border border-[var(--border)] bg-[var(--surface)] px-3 py-1 text-xs font-black text-[var(--muted)]">{{ $chip }}</span>
                @endforeach
            </div>
        </div>

        <div>
            <p class="font-black text-[var(--text)]">Quick Links</p>
            <div class="mt-4 grid gap-2 text-sm font-semibold text-[var(--muted)]">
                <a class="hover:text-[var(--accent)]" href="{{ route('home') }}">Home</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('posts.index') }}">Articles</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('services') }}">Work With Me</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('about') }}">About</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('contact') }}">Contact</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('editorial-policy') }}">Editorial Policy</a>
            </div>
        </div>

        <div>
            <p class="font-black text-[var(--text)]">Categories</p>
            <div class="mt-4 grid gap-2 text-sm font-semibold text-[var(--muted)]">
                @foreach ($categories as $category)
                    <a class="hover:text-[var(--accent)]" href="{{ route('categories.show', $category) }}">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>

        <div>
            <p class="font-black text-[var(--text)]">Trust & Legal</p>
            <div class="mt-4 grid gap-2 text-sm font-semibold text-[var(--muted)]">
                <a class="hover:text-[var(--accent)]" href="{{ $brand['portfolio_url'] }}" rel="noopener noreferrer">Portfolio</a>
                @foreach ($socialLinks as $network => $url)
                    <a class="hover:text-[var(--accent)]" href="{{ $url }}" target="_blank" rel="noopener noreferrer me">{{ $socialLabels[$network] ?? Str::headline($network) }}</a>
                @endforeach
                <a class="hover:text-[var(--accent)]" href="{{ route('privacy') }}">Privacy Policy</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('terms') }}">Terms</a>
                <a class="hover:text-[var(--accent)]" href="{{ route('affiliate-disclosure') }}">Affiliate Disclosure</a>
            </div>
        </div>

        <div class="sm:col-span-2 lg:col-span-5 flex flex-col gap-3 border-t border-[var(--border)] pt-6 text-xs font-semibold text-[var(--muted)] sm:flex-row sm:items-center sm:justify-between">
            <span>{{ now()->year }} {{ $brand['name'] }}. {{ $brand['tagline'] }}</span>
            <a href="mailto:{{ $brand['email'] }}" class="break-all hover:text-[var(--accent)]">{{ $brand['email'] }}</a>
        </div>
    </div>
</footer>
