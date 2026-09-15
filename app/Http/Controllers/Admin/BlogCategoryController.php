<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.blog-categories.index', [
            'categories' => BlogCategory::query()->withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        BlogCategory::create($request->validated());

        return back()->with('status', 'Blog category created.');
    }

    public function update(StoreBlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update($request->validated());

        return back()->with('status', 'Blog category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();

        return back()->with('status', 'Blog category deleted.');
    }
}
