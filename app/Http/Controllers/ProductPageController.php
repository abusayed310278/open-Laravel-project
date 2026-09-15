<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPageController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->live()
            ->with(['images', 'category', 'brand', 'user'])
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%'))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->filled('brand'), fn ($query) => $query->whereHas('brand', fn ($b) => $b->where('slug', $request->string('brand'))))
            ->when($request->filled('condition'), fn ($query) => $query->where('condition', $request->string('condition')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->float('max_price')))
            ->tap(fn ($query) => match ($request->string('sort')->value()) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                default => $query->orderByDesc('published_at'),
            })
            ->paginate(20)
            ->withQueryString();

        return view('pages.shop', [
            'products' => $products,
            'categories' => Category::cachedTree()->sortBy('name')->values(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        $canPreview = auth()->user()?->isAdmin() || (auth()->check() && auth()->id() === $product->user_id);

        abort_unless($product->publication_status->value === 'published' || $canPreview, 404);

        if ($product->publication_status->value === 'published') {
            $product->increment('views_count');
        }

        $product->load([
            'images',
            'category.attributes.group',
            'category.attributes.values',
            'brand',
            'user.businessProfile',
            'user.salerProfile',
            'attributeValues.attribute.group',
            'attributeValues.attributeValue',
        ]);

        $seller = $product->user;
        $sellerProfile = $seller->businessProfile ?? $seller->salerProfile;

        return view('pages.product', [
            'product' => $product,
            'isPreview' => $product->publication_status->value !== 'published' && $canPreview,
            'sellerProfile' => $sellerProfile,
            'sellerName' => $sellerProfile->business_name ?? $sellerProfile->display_name ?? $seller->name,
            'sellerLocation' => $sellerProfile->city ?? null,
            'sellerRating' => (float) $seller->approvedReviewsAsSeller()->avg('rating'),
            'sellerReviewCount' => $seller->approvedReviewsAsSeller()->count(),
            'sellerUrl' => match (true) {
                ! $sellerProfile => null,
                $seller->isBusiness() => route('stores.business', $sellerProfile->slug),
                $seller->isSaler() => route('stores.saler', $sellerProfile->slug),
                default => null,
            },
            'related' => Product::query()
                ->live()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with('images')
                ->limit(4)
                ->get(),
            'moreFromSeller' => Product::query()
                ->live()
                ->where('user_id', $seller->id)
                ->where('id', '!=', $product->id)
                ->with('images')
                ->limit(4)
                ->get(),
            'reviews' => $product->approvedReviews()->with('reviewer')->latest()->limit(10)->get(),
            'reviewCount' => $product->approvedReviews()->count(),
            'averageRating' => (float) $product->approvedReviews()->avg('rating'),
        ]);
    }
}
