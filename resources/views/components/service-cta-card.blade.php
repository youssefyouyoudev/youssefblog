@props(['title', 'description', 'url' => null, 'label' => 'Discuss this build'])

<article {{ $attributes->merge(['class' => 'rounded-lg border border-black/10 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-500 hover:shadow-lg']) }}>
    <p class="text-xs font-black uppercase tracking-wide text-emerald-600">Service</p>
    <h3 class="mt-3 text-xl font-black">{{ $title }}</h3>
    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $description }}</p>
    {{ $slot }}
    <a href="{{ $url ?: config('brand.start_project_url') }}" class="mt-5 inline-flex rounded-lg bg-black px-4 py-2 text-sm font-black text-white">{{ $label }}</a>
</article>
