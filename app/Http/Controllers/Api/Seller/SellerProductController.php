<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\ProductApprovalStatus;
use App\Enums\ProductCondition;
use App\Enums\ProductPublicationStatus;
use App\Enums\ProductStatus;
use App\Enums\ShippingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\SubscriptionService;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly SubscriptionService $subscriptions,
    ) {}

    /**
     * List products owned by authenticated seller with filter tabs and search.
     */
    public function index(Request $request): JsonResponse
    {
        $seller = Auth::user();
        $query = $seller->products()->with(['category', 'brand', 'images']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->string('status')->value();
            if ($status === 'published') {
                $query->where('publication_status', ProductPublicationStatus::Published->value);
            } elseif ($status === 'draft') {
                $query->where('status', ProductStatus::Draft->value);
            } elseif ($status === 'pending_approval') {
                $query->where('approval_status', ProductApprovalStatus::Pending->value);
            } elseif ($status === 'rejected') {
                $query->where('approval_status', ProductApprovalStatus::Rejected->value);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $paginator = $query->latest()->paginate((int) $request->input('per_page', 15));

        // Status counts for mobile segment tabs
        $counts = [
            'all' => $seller->products()->count(),
            'published' => $seller->products()->where('publication_status', ProductPublicationStatus::Published->value)->count(),
            'draft' => $seller->products()->where('status', ProductStatus::Draft->value)->count(),
            'pending_approval' => $seller->products()->where('approval_status', ProductApprovalStatus::Pending->value)->count(),
            'rejected' => $seller->products()->where('approval_status', ProductApprovalStatus::Rejected->value)->count(),
        ];

        $items = $paginator->getCollection()->map(function (Product $product) {
            $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            $thumbnail = $primaryImage ? MediaUrl::resolve($primaryImage->image_path) : null;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'category_name' => $product->category?->name,
                'brand_name' => $product->brand?->name,
                'price' => (float) $product->price,
                'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
                'quantity' => (int) $product->quantity,
                'condition' => $product->condition?->value,
                'condition_label' => ucfirst(str_replace('_', ' ', $product->condition?->value ?? '')),
                'status' => $product->status?->value,
                'approval_status' => $product->approval_status?->value,
                'publication_status' => $product->publication_status?->value,
                'is_published' => $product->publication_status === ProductPublicationStatus::Published,
                'thumbnail' => $thumbnail,
                'views_count' => (int) $product->views_count,
                'created_at' => $product->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'products' => $items,
            'counts' => $counts,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Categories, brands, conditions, and shipping options for form creation.
     */
    public function meta(): JsonResponse
    {
        $categories = Category::query()
            ->active()
            ->with(['attributes.values', 'attributes.group'])
            ->orderBy('name')
            ->get()
            ->map(fn (Category $cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'parent_id' => $cat->parent_id,
                'attributes' => $cat->attributes->map(fn ($attr) => [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type?->value ?? 'text',
                    'is_required' => (bool) ($attr->pivot->is_required ?? false),
                    'values' => $attr->values->map(fn ($v) => [
                        'id' => $v->id,
                        'value' => $v->value,
                    ]),
                ]),
            ]);

        $brands = Brand::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo_url' => $brand->logo ? MediaUrl::resolve($brand->logo) : null,
            ]);

        $conditions = array_map(fn (ProductCondition $c) => [
            'value' => $c->value,
            'label' => ucfirst(str_replace('_', ' ', $c->value)),
        ], ProductCondition::cases());

        $shippingTypes = array_map(fn (ShippingType $s) => [
            'value' => $s->value,
            'label' => ucfirst(str_replace('_', ' ', $s->value)),
        ], ShippingType::cases());

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'conditions' => $conditions,
            'shipping_types' => $shippingTypes,
        ]);
    }

    /**
     * Get product details for viewing or editing.
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product->load(['category', 'brand', 'images', 'attributeValues.attribute']);

        $images = $product->images->map(fn ($img) => [
            'id' => $img->id,
            'url' => MediaUrl::resolve($img->image_path),
            'is_primary' => (bool) $img->is_primary,
            'sort_order' => $img->sort_order,
        ]);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'brand_id' => $product->brand_id,
                'brand_name' => $product->brand?->name,
                'condition' => $product->condition?->value,
                'grade' => $product->grade?->value,
                'price' => (float) $product->price,
                'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
                'sku' => $product->sku,
                'quantity' => (int) $product->quantity,
                'is_negotiable' => (bool) $product->is_negotiable,
                'weight' => $product->weight ? (float) $product->weight : null,
                'shipping_type' => $product->shipping_type?->value,
                'shipping_flat_rate' => $product->shipping_flat_rate ? (float) $product->shipping_flat_rate : null,
                'status' => $product->status?->value,
                'approval_status' => $product->approval_status?->value,
                'publication_status' => $product->publication_status?->value,
                'rejection_reason' => $product->rejection_reason,
                'is_locked' => $product->isLocked(),
                'images' => $images,
                'attributes' => $product->attributeValues->map(fn ($av) => [
                    'attribute_id' => $av->attribute_id,
                    'attribute_name' => $av->attribute?->name,
                    'value_id' => $av->id,
                    'value' => $av->value,
                ]),
                'created_at' => $product->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Create a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create(
            $request->user(),
            $request->safe()->except(['images', 'attributes', 'primary_image_id', 'primary_image_index', 'delete_images']),
            $request->file('images', []),
            $request->input('attributes', []),
            $request->input('primary_image_index') !== null ? (int) $request->input('primary_image_index') : null,
        );

        $this->products->submitForApproval($product);

        return response()->json([
            'message' => "\"{$product->title}\" was successfully created and submitted for review.",
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'status' => $product->status->value,
                'approval_status' => $product->approval_status->value,
            ],
        ], 201);
    }

    /**
     * Update an existing product.
     */
    public function update(StoreProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $this->products->update(
            $product,
            $request->safe()->except(['images', 'attributes', 'primary_image_id', 'primary_image_index', 'delete_images']),
            $request->file('images', []),
            $request->input('attributes', []),
            $request->user(),
            $request->input('primary_image_id') ? (int) $request->input('primary_image_id') : null,
            $request->input('primary_image_index') !== null ? (int) $request->input('primary_image_index') : null,
            array_map('intval', (array) $request->input('delete_images', []))
        );

        if (in_array($product->status, [ProductStatus::Draft, ProductStatus::Rejected], true)) {
            $this->products->submitForApproval($product);
        }

        return response()->json([
            'message' => 'Listing updated successfully.',
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'status' => $product->status->value,
            ],
        ]);
    }

    /**
     * Toggle product publish status.
     */
    public function togglePublish(Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        if ($product->publication_status === ProductPublicationStatus::Published) {
            $this->products->unpublish($product);
            return response()->json([
                'message' => "\"{$product->title}\" is now unpublished.",
                'is_published' => false,
            ]);
        }

        if ($product->approval_status !== ProductApprovalStatus::Approved) {
            return response()->json([
                'message' => 'This listing must be approved by admin before it can be published.',
            ], 422);
        }

        $owner = $product->user ?? Auth::user();
        if (! $this->subscriptions->canPublish($owner)) {
            $message = $owner->isSaler()
                ? 'You have no listing credits left. A subscription plan is required to publish more listings.'
                : 'An active subscription plan is required to publish more listings.';

            return response()->json([
                'message' => $message,
            ], 422);
        }

        $this->products->publish($product);
        $this->subscriptions->recordPublish($owner, $product);

        return response()->json([
            'message' => "\"{$product->title}\" is now live on the marketplace.",
            'is_published' => true,
        ]);
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return response()->json([
            'message' => 'Listing deleted successfully.',
        ]);
    }
}
