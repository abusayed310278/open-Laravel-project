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
        return view('pages.category', [
            'category' => $category->load('children', 'parent'),
        ]);
    }
}
