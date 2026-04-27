@props(['compact' => false])

@php($photo = config('brand.author_photo'))

<section {{ $attributes->merge(['class' => 'rounded-3xl border border-blue-100 bg-white p-6 shadow-soft']) }}>
    <div class="flex gap-4 {{ $compact ? 'items-center' : 'items-start' }}">
        @if ($photo)
            <img src="{{ $photo }}" alt="Youssef Youyou" class="h-16 w-16 rounded-full object-cover" width="64" height="64" loading="lazy">
        @else
            <div class="{{ $compact ? 'h-14 w-14' : 'h-20 w-20' }} flex shrink-0 items-center justify-center rounded-full bg-blue-600 text-xl font-black text-white">YY</div>
        @endif
        <div>
            <p class="text-xl font-black text-gray-900">Youssef Youyou</p>
            <p class="mt-1 text-sm font-bold text-blue-600">Senior Full-Stack Laravel Developer · Morocco</p>
            <p class="mt-3 text-sm leading-6 text-gray-600">I build production websites, SaaS platforms, dashboards, and AI workflows. Everything on this blog comes from real project experience.</p>
            @unless ($compact)
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ config('brand.portfolio_url') }}" class="premium-button bg-blue-600 text-white" rel="noopener noreferrer">View Portfolio</a>
                    <a href="{{ config('brand.start_project_url') }}" class="premium-button border border-gray-200 bg-white text-gray-900" rel="noopener noreferrer">Start a Project</a>
                </div>
            @endunless
        </div>
    </div>
</section>
