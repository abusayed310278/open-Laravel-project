<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['user', 'category', 'images'])
            ->when($request->filled('status'), fn ($query) => $query->where('approval_status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'statuses' => ProductApprovalStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
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

        // Admin's own inventory doesn't need admin approval of itself.
        $this->products->approve($product);

        return redirect()->route('admin.products.index')->with('status', "\"{$product->title}\" was created.");
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load(['images', 'attributeValues']),
            'categories' => Category::query()->active()->with(['attributes.values', 'attributes.group'])->orderBy('name')->get(),
            'brands' => Brand::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update(
            $product,
            $request->safe()->except(['images', 'attributes']),
            $request->file('images', []),
            $request->input('attributes', []),
            $request->user(),
        );

        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }

    public function approve(Product $product): RedirectResponse
    {
        $this->products->approve($product);

        return back()->with('status', "\"{$product->title}\" was approved.");
    }

    public function publish(Product $product): RedirectResponse
    {
        $this->products->publish($product);

        return back()->with('status', "\"{$product->title}\" is now published.");
    }

    public function unpublish(Product $product): RedirectResponse
    {
        $this->products->unpublish($product);

        return back()->with('status', "\"{$product->title}\" was unpublished.");
    }

    public function togglePublish(Product $product): RedirectResponse
    {
        if ($product->publication_status?->value === 'published') {
            $this->products->unpublish($product);
            $message = "\"{$product->title}\" was unpublished.";
        } else {
            $this->products->publish($product);
            $message = "\"{$product->title}\" is now published.";
        }

        return back()->with('status', $message);
    }

    public function reject(RejectProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->reject($product, $request->string('reason')->value());

        return back()->with('status', "\"{$product->title}\" was rejected.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }
}
