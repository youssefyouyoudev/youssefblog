{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ([route('home'), route('posts.index'), route('tools.index'), route('money.index'), route('services'), route('services.alias'), route('about'), route('contact'), route('privacy'), route('terms'), route('editorial-policy'), route('affiliate-disclosure')] as $url)
        <url><loc>{{ $url }}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
    @endforeach
    @foreach ($moneyPages as $page)
        <url><loc>{{ route('money.show', $page['slug']) }}</loc><changefreq>monthly</changefreq><priority>0.8</priority></url>
    @endforeach
    @foreach ($categories as $category)
        <url><loc>{{ route('categories.show', $category) }}</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>
    @endforeach
    @foreach ($tags as $tag)
        <url><loc>{{ route('tags.show', $tag) }}</loc><changefreq>weekly</changefreq><priority>0.5</priority></url>
    @endforeach
    @foreach ($posts as $post)
        <url><loc>{{ route('posts.show', $post) }}</loc><lastmod>{{ $post->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.9</priority></url>
    @endforeach
</urlset>
