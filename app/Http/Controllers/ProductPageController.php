<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Product;
use App\Models\SalerProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProductPageController extends Controller
{
    public function advanced(Request $request): View|RedirectResponse
    {
        $mode = in_array($request->string('mode')->value(), ['items', 'seller', 'item_number', 'store_items', 'stores'], true)
            ? $request->string('mode')->value()
            : 'items';

        if ($mode === 'item_number' && $request->filled('item_number')) {
            $number = trim($request->string('item_number')->value());

            $product = Product::query()
                ->live()
                ->where(fn (Builder $q) => $q->where('sku', $number)->when(ctype_digit($number), fn (Builder $w) => $w->orWhere('id', (int) $number)))
                ->first();

            return $product
                ? redirect()->route('products.show', $product)
                : back()->withInput()->withErrors(['item_number' => 'No product found with that item number or SKU.']);
        }

        return view('pages.advanced-search', [
            'mode' => $mode,
            'categories' => Category::cachedTree()->sortBy('name')->values(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
            'stores' => $mode === 'stores' && $request->hasAny(['q', 'location']) ? $this->searchStores($request) : null,
        ]);
    }

    /**
     * @return Collection<int, array{name: string, location: string, logo: ?string, url: string}>
     */
    private function searchStores(Request $request): Collection
    {
        $name = trim($request->string('q')->value());
        $location = trim($request->string('location')->value());

        $businesses = BusinessProfile::query()
            ->where('is_store_active', true)
            ->when($name !== '', fn (Builder $q) => $q->where('business_name', 'like', '%'.$name.'%'))
            ->when($location !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->where('city', 'like', '%'.$location.'%')->orWhere('country', 'like', '%'.$location.'%')))
            ->get()
            ->map(fn (BusinessProfile $profile) => [
                'name' => $profile->business_name,
                'location' => implode(', ', array_filter([$profile->city, $profile->country])),
                'logo' => $profile->logoUrl(),
                'url' => route('stores.business', $profile->slug),
            ]);

        $salers = SalerProfile::query()
            ->where('is_store_active', true)
            ->when($name !== '', fn (Builder $q) => $q->where('display_name', 'like', '%'.$name.'%'))
            ->when($location !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->where('city', 'like', '%'.$location.'%')->orWhere('country', 'like', '%'.$location.'%')->orWhere('location', 'like', '%'.$location.'%')))
            ->get()
            ->map(fn (SalerProfile $profile) => [
                'name' => $profile->display_name,
                'location' => $profile->location ?: implode(', ', array_filter([$profile->city, $profile->country])),
                'logo' => $profile->logoUrl(),
                'url' => route('stores.saler', $profile->slug),
            ]);

        return $businesses->concat($salers)->sortBy('name')->values();
    }

    public function index(Request $request): View
    {
        $perPage = in_array($request->integer('per_page'), [20, 40, 60], true) ? $request->integer('per_page') : 20;

        $products = Product::query()
            ->live()
            ->with(['images', 'category', 'brand', 'user'])
            ->tap(fn ($query) => $this->applyKeywordFilters($query, $request))
            ->when($request->filled('seller'), fn ($query) => $query->whereHas('user', fn ($u) => $u
                ->where('name', 'like', '%'.$request->string('seller').'%')
                ->orWhereHas('businessProfile', fn ($b) => $b->where('business_name', 'like', '%'.$request->string('seller').'%'))
                ->orWhereHas('salerProfile', fn ($b) => $b->where('display_name', 'like', '%'.$request->string('seller').'%'))))
            ->when($request->boolean('stores_only'), fn ($query) => $query->whereHas('user', fn ($u) => $u
                ->whereHas('businessProfile', fn ($b) => $b->where('is_store_active', true))
                ->orWhereHas('salerProfile', fn ($b) => $b->where('is_store_active', true))))
            ->when($request->filled('category'), fn ($query) => $query->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->filled('brand'), fn ($query) => $query->whereHas('brand', fn ($b) => $b->where('slug', $request->string('brand'))))
            ->when($request->filled('condition'), fn ($query) => $query->whereIn('condition', Arr::wrap($request->input('condition'))))
            ->when($request->filled('grade'), fn ($query) => $query->whereIn('grade', Arr::wrap($request->input('grade'))))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->float('max_price')))
            ->when($request->boolean('negotiable'), fn ($query) => $query->where('is_negotiable', true))
            ->when($request->boolean('free_shipping'), fn ($query) => $query->where('shipping_type', 'free'))
            ->when($request->boolean('in_stock'), fn ($query) => $query->where('quantity', '>', 0))
            ->tap(fn ($query) => match ($request->string('sort')->value()) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'oldest' => $query->orderBy('published_at'),
                default => $query->orderByDesc('published_at'),
            })
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.shop', [
            'products' => $products,
            'categories' => Category::cachedTree()->sortBy('name')->values(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Apply the keyword, match-mode, description and exclusion filters.
     *
     * @param  Builder<Product>  $query
     */
    private function applyKeywordFilters(Builder $query, Request $request): void
    {
        $columns = ['title', 'short_description', 'description'];

        $matches = fn (Builder $q, string $term) => $q->where(function (Builder $inner) use ($columns, $term): void {
            foreach ($columns as $column) {
                $inner->orWhere($column, 'like', '%'.$term.'%');
            }

            $inner->orWhere('sku', 'like', '%'.$term.'%');

            $inner->orWhereHas('brand', function (Builder $b) use ($term): void {
                $b->where('name', 'like', '%'.$term.'%');
            });

            $inner->orWhereHas('category', function (Builder $c) use ($term): void {
                $c->where('name', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%');
            });
        });

        $keywords = trim($request->string('q')->value());

        if ($keywords !== '') {
            $words = preg_split('/\s+/', $keywords) ?: [];

            match ($request->string('match')->value()) {
                'exact' => $matches($query, $keywords),
                'any' => $query->where(fn (Builder $q) => collect($words)->each(fn ($word) => $q->orWhere(fn (Builder $w) => $matches($w, $word)))),
                default => collect($words)->each(fn ($word) => $matches($query, $word)),
            };
        }

        $excluded = preg_split('/[\s,]+/', trim($request->string('exclude')->value()), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($excluded as $word) {
            $query->where(function (Builder $q) use ($columns, $word): void {
                foreach ($columns as $column) {
                    $q->where(fn (Builder $c) => $c->whereNull($column)->orWhere($column, 'not like', '%'.$word.'%'));
                }
            });
        }
    }

    public function show(Product $product): View
    {
        $canPreview = auth()->user()?->isAdmin() || auth()->user()?->isVerifier() || (auth()->check() && auth()->id() === $product->user_id);

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
        $isAdminProduct = $seller->isAdmin();

        $sellerName = match (true) {
            $isAdminProduct => config('app.name', 'Openbox') . ' Official',
            default => $sellerProfile->business_name ?? $sellerProfile->display_name ?? $seller->name,
        };

        $sellerLogoUrl = match (true) {
            $isAdminProduct => \App\Support\MediaUrl::resolve(setting('site_icon') ?: setting('brand_logo')) ?: asset('buy-and-sale.png'),
            default => $sellerProfile?->logoUrl(),
        };

        $sellerUrl = match (true) {
            $isAdminProduct => route('home'),
            ! $sellerProfile => null,
            $seller->isBusiness() => route('stores.business', $sellerProfile->slug),
            $seller->isSaler() => route('stores.saler', $sellerProfile->slug),
            default => null,
        };

        return view('pages.product', [
            'product' => $product,
            'isPreview' => $product->publication_status->value !== 'published' && $canPreview,
            'sellerProfile' => $sellerProfile,
            'sellerName' => $sellerName,
            'sellerLogoUrl' => $sellerLogoUrl,
            'isAdminProduct' => $isAdminProduct,
            'sellerLocation' => $sellerProfile->city ?? null,
            'sellerRating' => (float) $seller->approvedReviewsAsSeller()->avg('rating'),
            'sellerReviewCount' => $seller->approvedReviewsAsSeller()->count(),
            'sellerUrl' => $sellerUrl,
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
