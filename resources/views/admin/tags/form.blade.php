<label class="text-sm font-bold">Name</label>
<input name="name" value="{{ old('name', $tag->name) }}" oninput="if(!document.querySelector('[name=slug]').dataset.edited){document.querySelector('[name=slug]').value=window.slugify(this.value)}" class="mt-2 w-full border border-black/10 px-3 py-3" required>
@error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
<label class="mt-4 block text-sm font-bold">Slug</label>
<input name="slug" data-edited="0" oninput="this.dataset.edited=1" value="{{ old('slug', $tag->slug) }}" class="mt-2 w-full border border-black/10 px-3 py-3" required>
@error('slug')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
<button class="mt-5 rounded-lg bg-black px-5 py-3 font-black text-white">{{ $button }}</button>
