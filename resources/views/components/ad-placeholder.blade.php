@props(['label' => 'Ad placement'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-emerald-400 bg-emerald-50 p-6 text-center shadow-sm']) }}>
    <p class="text-xs font-black uppercase tracking-wide text-emerald-700">{{ $label }}</p>
    <p class="mt-2 text-sm text-slate-600">AdSense placeholder. Replace with real ad code after approval.</p>
</div>
