@props(['name', 'description', 'url', 'count' => null, 'icon' => null])

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'group rounded-2xl border border-black/10 bg-white p-6 shadow-soft transition duration-300 hover:-translate-y-1 hover:border-emerald-500 hover:shadow-glow']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-black text-lg font-black text-brand transition group-hover:bg-brand group-hover:text-black">{{ $icon ?: substr($name, 0, 1) }}</span>
    <span class="mt-5 block text-xl font-black text-ink">{{ $name }}</span>
    <span class="mt-3 block text-sm leading-6 text-slate-600">{{ $description }}</span>
    @if (! is_null($count))
        <span class="mt-5 inline-flex rounded-full border border-black/10 px-3 py-1 text-xs font-black uppercase text-slate-500">{{ $count }} guides</span>
    @endif
</a>
