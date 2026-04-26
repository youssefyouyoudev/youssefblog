{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0">
    <channel>
        <title>Youssef Blog</title>
        <link>{{ route('home') }}</link>
        <description>Finance, Tech, AI, Laravel, and Online Business guides.</description>
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('posts.show', $post) }}</link>
                <guid>{{ route('posts.show', $post) }}</guid>
                <description>{{ $post->excerpt }}</description>
                <category>{{ $post->category->name }}</category>
                <pubDate>{{ $post->published_at?->toRssString() }}</pubDate>
            </item>
        @endforeach
    </channel>
</rss>
