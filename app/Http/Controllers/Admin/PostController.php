<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::query()
            ->with('category')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->latest('published_at')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(),
            'statusFilter' => $request->string('status')->toString(),
            'categoryFilter' => $request->integer('category_id'),
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

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:publish,reschedule,delete'],
            'posts' => ['required', 'array'],
            'posts.*' => ['integer', 'exists:posts,id'],
            'published_at' => ['nullable', 'date'],
        ]);

        $posts = Post::whereIn('id', $data['posts'])->get();

        if ($data['action'] === 'publish') {
            $posts->each->update(['status' => 'published', 'published_at' => now()]);
        }

        if ($data['action'] === 'reschedule') {
            $posts->each->update(['status' => 'scheduled', 'published_at' => $data['published_at'] ?? now()->addDay()]);
        }

        if ($data['action'] === 'delete') {
            $posts->each->delete();
        }

        return back()->with('status', 'Bulk action completed.');
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
        $data['faqs'] = collect(preg_split('/\r\n|\r|\n/', $request->validated('faqs') ?? ''))
            ->map(fn (string $line): array => array_pad(array_map('trim', explode('|', $line, 2)), 2, null))
            ->filter(fn (array $faq): bool => filled($faq[0]) && filled($faq[1]))
            ->map(fn (array $faq): array => ['question' => $faq[0], 'answer' => $faq[1]])
            ->values()
            ->all();

        return $data;
    }
}
