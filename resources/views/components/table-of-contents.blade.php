@props(['headings'])

@if ($headings->isNotEmpty())
    <nav {{ $attributes->merge(['class' => 'rounded-2xl border border-black/10 bg-white p-5 shadow-soft']) }}>
        <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-600">Table of contents</p>
        <div class="mt-4 grid gap-2 text-sm font-bold text-slate-700">
            @foreach ($headings as $heading)
                <a class="toc-link rounded-lg px-2 py-1 transition hover:bg-emerald-50 hover:text-emerald-700" href="#{{ Str::slug($heading) }}">{{ $heading }}</a>
            @endforeach
        </div>
    </nav>
@endif
