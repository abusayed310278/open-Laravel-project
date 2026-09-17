<?php

namespace App\Http\Controllers;

use App\Enums\ProductApprovalStatus;
use App\Enums\UserRole;
use App\Http\Requests\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly SubscriptionService $subscriptions,
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();

        // Store owners (business role) can see only individual seller (saler role) products;
        // Individual sellers (saler role) see their own listings.
        $query = $user->isBusiness()
            ? Product::query()->whereHas('user', fn ($q) => $q->where('role', UserRole::Saler))->with(['category', 'images', 'user'])
            : $user->products()->with(['category', 'images']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        return view('seller.products.index', [
            'products' => $query->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('seller.products.create', [
            'product' => new Product,
            'categories' => Category::query()->active()->with(['attributes.values', 'attributes.group'])->orderBy('name')->get(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->products->create(
            $request->user(),
            $request->safe()->except(['images', 'attributes']),
            $request->file('images', []),
            $request->input('attributes', []),
        );

        return redirect()->route($this->indexRoute())->with('status', "\"{$product->title}\" was saved as a draft.");
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('seller.products.edit', [
            'product' => $product->load(['images', 'attributeValues']),
            'categories' => Category::query()->active()->with(['attributes.values', 'attributes.group'])->orderBy('name')->get(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->update(
            $product,
            $request->safe()->except(['images', 'attributes']),
            $request->file('images', []),
            $request->input('attributes', []),
            $request->user(),
        );

        return redirect()->route($this->indexRoute())->with('status', 'Listing updated.');
    }

    public function submit(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->submitForApproval($product);

        return back()->with('status', 'Submitted for review.');
    }

    public function publish(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        if ($product->approval_status !== ProductApprovalStatus::Approved) {
            return back()->withErrors(['product' => 'This listing must be approved before it can be published.']);
        }

        $owner = $product->user ?? Auth::user();

        if (! $this->subscriptions->canPublish($owner)) {
            $message = $owner->isSaler()
                ? 'The seller has no listing credits left. A subscription plan is required to publish more listings.'
                : 'An active subscription is required to publish more listings.';

            return back()->withErrors(['product' => $message]);
        }

        $this->products->publish($product);
        $this->subscriptions->recordPublish($owner, $product);

        return back()->with('status', "\"{$product->title}\" is now live.");
    }

    public function unpublish(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->products->unpublish($product);

        return back()->with('status', "\"{$product->title}\" was unpublished.");
    }

    public function togglePublish(Product $product): RedirectResponse
    {
        if ($product->publication_status?->value === 'published') {
            return $this->unpublish($product);
        }

        return $this->publish($product);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return redirect()->route($this->indexRoute())->with('status', 'Listing deleted.');
    }

    private function indexRoute(): string
    {
        return Auth::user()->isBusiness() ? 'business.products.index' : 'saler.products.index';
    }
}
