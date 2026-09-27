<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'brand', 'user.businessProfile', 'user.salerProfile'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products)->response();
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'brand', 'user.businessProfile', 'user.salerProfile', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->firstOrFail();

        return (new ProductResource($product))->response();
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'slug', 'image']);

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function brands(): JsonResponse
    {
        $brands = Brand::query()->orderBy('name')->get(['id', 'name', 'slug', 'logo']);

        return response()->json([
            'data' => $brands,
        ]);
    }
}
