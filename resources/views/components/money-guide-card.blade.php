@props(['page'])

<a href="{{ route('money.show', $page['slug']) }}" {{ $attributes->merge(['class' => 'group rounded-2xl border border-black/10 bg-white p-5 shadow-soft transition duration-300 hover:-translate-y-1 hover:border-emerald-500 hover:shadow-glow']) }}>
    <span class="category-pill">{{ $page['category'] }}</span>
    <span class="mt-4 block text-lg font-black text-ink transition group-hover:text-emerald-700">{{ $page['title'] }}</span>
    <span class="mt-3 line-clamp-3 block text-sm leading-6 text-slate-600">{{ $page['excerpt'] }}</span>
</a>
