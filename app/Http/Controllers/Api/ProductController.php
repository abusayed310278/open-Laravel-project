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
            ->with(['category', 'brand', 'user.profile', 'user.businessProfile', 'user.salerProfile'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->integer('brand_id')))
            ->when($request->filled('seller_id'), fn ($q) => $q->where('user_id', $request->integer('seller_id')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ProductResource::collection($products)->response();
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'brand', 'user.profile', 'user.businessProfile', 'user.salerProfile', 'reviews.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->firstOrFail();

        return (new ProductResource($product))->response();
    }

    public function seller(int $id): JsonResponse
    {
        $user = \App\Models\User::query()
            ->with(['profile', 'businessProfile', 'salerProfile'])
            ->withCount('products')
            ->findOrFail($id);

        $sellerLogo = null;
        if ($user->isBusiness() && $user->businessProfile?->logo) {
            $sellerLogo = \App\Support\MediaUrl::resolve($user->businessProfile->logo);
        } elseif ($user->isSaler() && $user->salerProfile?->logo) {
            $sellerLogo = \App\Support\MediaUrl::resolve($user->salerProfile->logo);
        }
        if (! $sellerLogo && $user->profile?->avatar) {
            $sellerLogo = \App\Support\MediaUrl::resolve($user->profile->avatar);
        }
        if (! $sellerLogo && $user->isAdmin()) {
            $brandLogo = setting('brand_logo');
            $sellerLogo = $brandLogo ? \App\Support\MediaUrl::resolve($brandLogo) : \App\Support\MediaUrl::resolve('icon.png');
        }

        $bannerUrl = null;
        if ($user->isAdmin()) {
            $bannerSetting = setting('brand_banner') ?: setting('brand_cover_image');
            $bannerUrl = $bannerSetting ? \App\Support\MediaUrl::resolve($bannerSetting) : (file_exists(public_path('banner image.png')) ? asset('banner image.png') : null);
        } elseif ($user->isBusiness() && $user->businessProfile?->cover_image) {
            $bannerUrl = \App\Support\MediaUrl::resolve($user->businessProfile->cover_image);
        } elseif ($user->isSaler() && $user->salerProfile?->cover_image) {
            $bannerUrl = \App\Support\MediaUrl::resolve($user->salerProfile->cover_image);
        }
        if (! $bannerUrl && file_exists(public_path('banner image.png'))) {
            $bannerUrl = asset('banner image.png');
        }

        $city = $user->profile?->location 
            ?: ($user->businessProfile?->city ?: ($user->salerProfile?->city ?: 'Doha, Qatar'));

        $sellerProductIds = Product::where('user_id', $user->id)->pluck('id');
        $reviewsCount = \App\Models\Review::whereIn('product_id', $sellerProductIds)->count();
        $avgRating = \App\Models\Review::whereIn('product_id', $sellerProductIds)->avg('rating');
        if (! $avgRating) {
            $avgRating = 4.8;
        }
        if ($reviewsCount === 0) {
            $reviewsCount = $user->products_count * 3;
        }

        return response()->json([
            'seller' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'logo_url' => $sellerLogo,
                'avatar_url' => $sellerLogo,
                'banner_url' => $bannerUrl,
                'cover_url' => $bannerUrl,
                'city' => $city,
                'rating' => round((float) $avgRating, 1),
                'review_count' => (int) $reviewsCount,
                'product_count' => (int) $user->products_count,
                'is_verified' => $user->isAdmin() || $user->isKycApproved(),
                'member_since' => $user->created_at?->format('M Y') ?? '2024',
            ],
        ]);
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
