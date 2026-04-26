@props(['label' => 'Ad placement'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-emerald-400/70 bg-emerald-50/80 p-6 text-center shadow-soft']) }}>
    <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-700">{{ $label }}</p>
    <p class="mt-2 text-sm text-slate-600">Clean AdSense placeholder. Real ads can be added after approval.</p>
</div>
