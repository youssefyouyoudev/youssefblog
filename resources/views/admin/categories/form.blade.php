<label class="text-sm font-bold">Name</label>
<input name="name" value="{{ old('name', $category->name) }}" oninput="if(!document.querySelector('[name=slug]').dataset.edited){document.querySelector('[name=slug]').value=window.slugify(this.value)}" class="mt-2 w-full border border-black/10 px-3 py-3" required>
@error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
<label class="mt-4 block text-sm font-bold">Slug</label>
<input name="slug" data-edited="0" oninput="this.dataset.edited=1" value="{{ old('slug', $category->slug) }}" class="mt-2 w-full border border-black/10 px-3 py-3" required>
@error('slug')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
<label class="mt-4 block text-sm font-bold">Description</label>
<textarea name="description" rows="4" class="mt-2 w-full border border-black/10 px-3 py-3">{{ old('description', $category->description) }}</textarea>
<label class="mt-4 block text-sm font-bold">SEO Title</label>
<input name="seo_title" value="{{ old('seo_title', $category->seo_title) }}" class="mt-2 w-full border border-black/10 px-3 py-3">
<label class="mt-4 block text-sm font-bold">Meta Description</label>
<textarea name="meta_description" rows="3" class="mt-2 w-full border border-black/10 px-3 py-3">{{ old('meta_description', $category->meta_description) }}</textarea>
<button class="mt-5 rounded-lg bg-black px-5 py-3 font-black text-white">{{ $button }}</button>
