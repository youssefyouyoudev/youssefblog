@props(['compact' => false])

@php
    $stats = [
        ['value' => '5+', 'label' => 'Years Experience'],
        ['value' => '25+', 'label' => 'Projects'],
        ['value' => 'Laravel', 'label' => 'SaaS / AI Systems'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'grid gap-3 sm:grid-cols-3']) }}>
    @foreach ($stats as $stat)
        <div class="reveal-card rounded-xl border border-white/10 {{ $compact ? 'bg-white/5' : 'bg-white/10' }} p-4 shadow-soft backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-brand/60">
            <p class="text-2xl font-black text-brand">{{ $stat['value'] }}</p>
            <p class="mt-1 text-xs font-black uppercase tracking-wide text-white/70">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>
