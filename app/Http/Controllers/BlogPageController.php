<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogPageController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.blog.index', [
            'posts' => Post::query()
                ->published()
                ->with(['category', 'author'])
                ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
                ->latest('published_at')
                ->paginate(9)
                ->withQueryString(),
            'categories' => BlogCategory::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->status->value === 'published', 404);

        $related = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->with(['category', 'author'])
            ->when($post->category_id, fn ($q) => $q->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$post->category_id]))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog.show', [
            'post' => $post->load(['category', 'author', 'tags', 'approvedComments.user']),
            'related' => $related,
        ]);
    }
}
