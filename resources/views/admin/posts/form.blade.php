@php $selectedTags = collect(old('tags', $post->exists ? $post->tags->pluck('id')->all() : []))->map(fn ($id) => (int) $id); @endphp
<div class="grid gap-6 lg:grid-cols-[1fr_340px]">
    <div class="space-y-5">
        <div class="rounded-lg border border-black/10 bg-white p-5">
            <label class="text-sm font-bold">Title</label>
            <input name="title" value="{{ old('title', $post->title) }}" oninput="if(!document.querySelector('[name=slug]').dataset.edited){document.querySelector('[name=slug]').value=window.slugify(this.value)}" class="mt-2 w-full border border-black/10 px-3 py-3" required>
            @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            <label class="mt-4 block text-sm font-bold">Slug</label>
            <input name="slug" data-edited="0" oninput="this.dataset.edited=1" value="{{ old('slug', $post->slug) }}" class="mt-2 w-full border border-black/10 px-3 py-3" required>
            <p class="mt-2 text-xs font-semibold text-slate-500">Preview: /posts/<span id="slug-preview">{{ old('slug', $post->slug) ?: 'your-post-slug' }}</span></p>
            @error('slug')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            <label class="mt-4 block text-sm font-bold">Excerpt</label>
            <textarea name="excerpt" rows="3" class="mt-2 w-full border border-black/10 px-3 py-3" required>{{ old('excerpt', $post->excerpt) }}</textarea>
            @error('excerpt')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            <label class="mt-4 block text-sm font-bold">Content</label>
            <textarea name="content" rows="16" class="mt-2 w-full border border-black/10 px-3 py-3 font-mono text-sm" required>{{ old('content', $post->content) }}</textarea>
            <p class="mt-2 text-xs font-semibold text-slate-500">Reading time auto-calculates on save. Use headings like: ## Main section</p>
            @error('content')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="rounded-lg border border-black/10 bg-white p-5">
            <h2 class="font-black">SEO</h2>
            @foreach (['seo_title' => 'SEO Title', 'meta_description' => 'Meta Description', 'canonical_url' => 'Canonical URL', 'og_image' => 'OG Image URL'] as $field => $label)
                <label class="mt-4 block text-sm font-bold">{{ $label }}</label>
                <input name="{{ $field }}" value="{{ old($field, $post->{$field}) }}" @if($field === 'meta_description') maxlength="320" oninput="document.getElementById('meta-count').textContent=this.value.length" @endif class="mt-2 w-full border border-black/10 px-3 py-3">
                @if ($field === 'meta_description')
                    <p class="mt-2 text-xs font-semibold text-slate-500"><span id="meta-count">{{ strlen(old('meta_description', $post->meta_description ?? '')) }}</span>/320 characters</p>
                @endif
                @error($field)<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @endforeach
            <label class="mt-4 block text-sm font-bold">SEO Keywords</label>
            <input name="keywords" value="{{ old('keywords', $post->keywordList()) }}" class="mt-2 w-full border border-black/10 px-3 py-3">
            <p class="mt-2 text-xs font-semibold text-slate-500">Comma-separated keywords, 5 to 10 is ideal.</p>
            <div class="mt-5 rounded-lg border border-black/10 bg-slate-50 p-4">
                <p class="text-xs font-black uppercase text-slate-500">Google preview</p>
                <p class="mt-2 text-lg text-blue-700">{{ old('seo_title', $post->seo_title) ?: old('title', $post->title) ?: 'SEO title preview' }}</p>
                <p class="text-sm text-emerald-700">{{ url('/posts/'.(old('slug', $post->slug) ?: 'your-post-slug')) }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ old('meta_description', $post->meta_description) ?: old('excerpt', $post->excerpt) ?: 'Meta description preview for search results.' }}</p>
            </div>
            <div class="mt-5 rounded-lg border border-black/10 bg-white p-4">
                <p class="text-xs font-black uppercase text-slate-500">SEO checklist</p>
                <ul class="mt-3 grid gap-2 text-sm text-slate-600">
                    <li>Title under 60 characters when possible</li>
                    <li>Meta description around 140-160 characters</li>
                    <li>Featured image alt text added</li>
                    <li>Keywords are natural and not stuffed</li>
                    <li>At least 2 useful headings in content</li>
                </ul>
            </div>
            <label class="mt-4 block text-sm font-bold">FAQs</label>
            <textarea name="faqs" rows="4" class="mt-2 w-full border border-black/10 px-3 py-3" placeholder="Question | Answer">{{ old('faqs', collect($post->faqs ?? [])->map(fn ($faq) => ($faq['question'] ?? '').' | '.($faq['answer'] ?? ''))->implode("\n")) }}</textarea>
            <p class="mt-2 text-xs font-semibold text-slate-500">One FAQ per line: Question | Answer</p>
        </div>
    </div>
    <aside class="space-y-5">
        <div class="rounded-lg border border-black/10 bg-white p-5">
            <label class="text-sm font-bold">Category</label>
            <select name="category_id" class="mt-2 w-full border border-black/10 px-3 py-3" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <label class="mt-4 block text-sm font-bold">Tags</label>
            <select name="tags[]" multiple class="mt-2 min-h-40 w-full border border-black/10 px-3 py-3">
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected($selectedTags->contains($tag->id))>{{ $tag->name }}</option>
                @endforeach
            </select>
            <label class="mt-4 block text-sm font-bold">Featured Image URL</label>
            <input name="featured_image" value="{{ old('featured_image', $post->featured_image) }}" oninput="document.getElementById('image-preview').src=this.value" class="mt-2 w-full border border-black/10 px-3 py-3">
            <img id="image-preview" src="{{ old('featured_image', $post->featured_image) ?: asset('assets/brand/youssef-blog-og.png') }}" alt="Image preview" class="mt-3 aspect-[16/9] w-full rounded-lg object-cover" width="320" height="180">
            <label class="mt-4 block text-sm font-bold">Featured Image Alt</label>
            <input name="featured_image_alt" value="{{ old('featured_image_alt', $post->featured_image_alt) }}" class="mt-2 w-full border border-black/10 px-3 py-3">
            <label class="mt-4 block text-sm font-bold">Image Credit</label>
            <input name="image_credit" value="{{ old('image_credit', $post->image_credit) }}" class="mt-2 w-full border border-black/10 px-3 py-3">
            <label class="mt-4 block text-sm font-bold">Status</label>
            <select name="status" class="mt-2 w-full border border-black/10 px-3 py-3">
                @foreach (['draft', 'published', 'scheduled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $post->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs font-black uppercase text-emerald-700">Current status: {{ ucfirst(old('status', $post->status ?? 'draft')) }}</p>
            <label class="mt-4 block text-sm font-bold">Published At</label>
            <input name="published_at" type="datetime-local" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="mt-2 w-full border border-black/10 px-3 py-3">
            <label class="mt-4 flex items-center gap-2 text-sm font-bold"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured))> Featured post</label>
        </div>
        <button class="w-full rounded-lg bg-black px-5 py-3 font-black text-white">Save Post</button>
    </aside>
</div>
<script>
    document.querySelector('[name=slug]')?.addEventListener('input', (event) => {
        document.getElementById('slug-preview').textContent = event.target.value || 'your-post-slug';
    });
</script>
