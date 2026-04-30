{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
@php($seo = app(\App\Services\SeoService::class))
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ([route('home'), route('posts.index'), route('services'), route('about'), route('contact'), route('privacy'), route('terms'), route('editorial-policy'), route('affiliate-disclosure')] as $url)
        <url><loc>{{ $seo->absoluteUrl($url) }}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
    @endforeach
    @foreach ($categories as $category)
        <url><loc>{{ $seo->absoluteUrl(route('categories.show', $category)) }}</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
    @endforeach
    @foreach ($posts as $post)
        <url><loc>{{ $seo->absoluteUrl(route('posts.show', $post)) }}</loc><lastmod>{{ $post->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.9</priority></url>
    @endforeach
</urlset>
