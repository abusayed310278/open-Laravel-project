<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Models\BlogCategory;
use App\Models\Post;
use App\Services\BlogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(private readonly BlogService $blog) {}

    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::query()->with(['category', 'author'])->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create', [
            'post' => new Post,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
            'statuses' => ContentStatus::cases(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->blog->create(
            Auth::user(),
            $request->safe()->except(['featured_image', 'tags']),
            $request->file('featured_image'),
            $request->string('tags')->value() ?: null,
        );

        return redirect()->route('admin.blog.index')->with('status', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', [
            'post' => $post->load('tags'),
            'categories' => BlogCategory::query()->orderBy('name')->get(),
            'statuses' => ContentStatus::cases(),
        ]);
    }

    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $this->blog->update(
            $post,
            $request->safe()->except(['featured_image', 'tags']),
            $request->file('featured_image'),
            $request->string('tags')->value() ?: null,
        );

        return redirect()->route('admin.blog.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return back()->with('status', 'Post deleted.');
    }
}
