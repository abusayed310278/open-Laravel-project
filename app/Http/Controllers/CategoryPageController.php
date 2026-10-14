<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryPageController extends Controller
{
    public function index(): View
    {
        return view('pages.categories', [
            'categories' => Category::cachedTree(),
        ]);
    }

    public function show(Category $category): View
    {
        $categoryIds = collect([$category->id])
            ->merge($category->children()->pluck('id'))
            ->all();

        $products = \App\Models\Product::query()
            ->live()
            ->whereIn('category_id', $categoryIds)
            ->with(['images', 'brand', 'user', 'category'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('pages.category', [
            'category' => $category->load('children', 'parent'),
            'products' => $products,
        ]);
    }
}
