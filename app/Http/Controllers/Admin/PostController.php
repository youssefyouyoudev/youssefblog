<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::with('category')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', $this->formData(new Post(['status' => 'draft'])));
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $data = $this->normalizedData($request);
        $data['user_id'] = $request->user()->id;

        $post = Post::create($data);
        $post->tags()->sync($request->validated('tags') ?? []);

        return redirect()->route('admin.posts.index')->with('status', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', $this->formData($post->load('tags')));
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $post->update($this->normalizedData($request));
        $post->tags()->sync($request->validated('tags') ?? []);

        return redirect()->route('admin.posts.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', 'Post deleted.');
    }

    private function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ];
    }

    private function normalizedData(PostRequest $request): array
    {
        $data = $request->validated();
        unset($data['tags']);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['reading_time'] = max(1, (int) ceil(Str::wordCount(strip_tags($data['content'])) / 220));
        $data['published_at'] = $data['published_at'] ?: ($data['status'] === 'published' ? now() : null);
        $data['keywords'] = collect(explode(',', $request->validated('keywords') ?? ''))
            ->map(fn (string $keyword): string => trim($keyword))
            ->filter()
            ->values()
            ->all();

        return $data;
    }
}
