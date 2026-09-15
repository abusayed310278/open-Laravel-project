<?php

namespace App\Services;

use App\Enums\PaymentRoute;
use App\Enums\ProductApprovalStatus;
use App\Enums\ProductCondition;
use App\Enums\ProductGrade;
use App\Enums\ProductPublicationStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductApproved;
use App\Notifications\ProductRejected;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @param  array<string, mixed>  $attributeValues  Keyed by attribute_id => attribute_value_id|string
     */
    public function create(User $seller, array $data, array $images = [], array $attributeValues = []): Product
    {
        return DB::transaction(function () use ($seller, $data, $images, $attributeValues) {
            $product = $seller->products()->create([
                ...$data,
                'payment_route' => $this->paymentRouteFor($seller),
                'status' => ProductStatus::Draft,
                // New (sealed/unused) items are always Grade A — only used
                // and refurbished items go through physical grading.
                'grade' => ($data['condition'] ?? null) === ProductCondition::New->value ? ProductGrade::A : ProductGrade::Ungraded,
            ]);

            $this->syncAttributeValues($product, $attributeValues);
            $this->addImages($product, $images);

            return $product->fresh(['images', 'attributeValues']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @param  array<string, mixed>  $attributeValues
     */
    public function update(Product $product, array $data, array $images = [], array $attributeValues = [], ?User $actor = null): Product
    {
        return DB::transaction(function () use ($product, $data, $images, $attributeValues, $actor) {
            $previousQuantity = $product->quantity;

            $product->update($data);

            if ($actor && array_key_exists('quantity', $data)) {
                $this->inventoryService->logAdjustment($product, $product->quantity - $previousQuantity, $actor, 'Manual edit');
            }

            $this->syncAttributeValues($product, $attributeValues);
            $this->addImages($product, $images);

            return $product->fresh(['images', 'attributeValues']);
        });
    }

    public function submitForApproval(Product $product): Product
    {
        $product->update([
            'status' => ProductStatus::PendingApproval,
            'approval_status' => ProductApprovalStatus::Pending,
        ]);

        ActivityLog::record('product.submitted', $product);

        return $product;
    }

    public function approve(Product $product): Product
    {
        $product->update([
            'status' => ProductStatus::Approved,
            'approval_status' => ProductApprovalStatus::Approved,
            'rejection_reason' => null,
        ]);

        $product->user->notify(new ProductApproved($product));

        ActivityLog::record('product.approved', $product);

        return $product;
    }

    public function reject(Product $product, string $reason): Product
    {
        $product->update([
            'status' => ProductStatus::Rejected,
            'approval_status' => ProductApprovalStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        $product->user->notify(new ProductRejected($product, $reason));

        ActivityLog::record('product.rejected', $product, ['reason' => $reason]);

        return $product;
    }

    /**
     * Publish an approved product to the live storefront. Listing-credit /
     * subscription gating for saler and business sellers lands in Phase 12
     * — admin-owned products are exempt from the start.
     */
    public function publish(Product $product): Product
    {
        $product->update([
            'status' => ProductStatus::Published,
            'publication_status' => ProductPublicationStatus::Published,
            'published_at' => now(),
        ]);

        ActivityLog::record('product.published', $product);

        return $product;
    }

    public function unpublish(Product $product): Product
    {
        $product->update([
            'status' => $product->approval_status === ProductApprovalStatus::Approved ? ProductStatus::Approved : ProductStatus::Draft,
            'publication_status' => ProductPublicationStatus::Unpublished,
        ]);

        ActivityLog::record('product.unpublished', $product);

        return $product;
    }

    public function delete(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();
    }

    /**
     * Admin-owned/warehouse-stored inventory always settles through the
     * Openbox gateway; everything else settles through the seller's own.
     */
    public function paymentRouteFor(User $seller): PaymentRoute
    {
        return $seller->role === UserRole::Admin ? PaymentRoute::Openbox : PaymentRoute::Seller;
    }

    /**
     * @param  array<string, mixed>  $attributeValues
     */
    private function syncAttributeValues(Product $product, array $attributeValues): void
    {
        if ($attributeValues === []) {
            return;
        }

        $product->attributeValues()->delete();

        $attributeModels = Attribute::query()->whereIn('id', array_keys($attributeValues))->get()->keyBy('id');

        foreach ($attributeValues as $attributeId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $attribute = $attributeModels->get((int) $attributeId);
            if (! $attribute) {
                continue;
            }

            if ($attribute->type->usesValueList()) {
                $product->attributeValues()->create([
                    'attribute_id' => $attribute->id,
                    'attribute_value_id' => (int) $value,
                ]);
            } else {
                $product->attributeValues()->create([
                    'attribute_id' => $attribute->id,
                    'custom_value' => (string) $value,
                ]);
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function addImages(Product $product, array $images): void
    {
        if ($images === []) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $nextSort = (int) ($product->images()->max('sort_order') ?? 0) + 1;

        foreach ($images as $index => $image) {
            if (! $image instanceof UploadedFile || ! $image->isValid()) {
                continue;
            }

            $filePath = $image->getRealPath() ?: $image->getPathname();
            if (! $filePath || ! file_exists($filePath)) {
                continue;
            }

            $extension = $image->guessExtension() ?: $image->getClientOriginalExtension() ?: 'jpg';
            $filename = Str::random(40).'.'.$extension;

            $storedPath = Storage::disk('public')->putFileAs('products', $filePath, $filename);

            if ($storedPath) {
                $product->images()->create([
                    'path' => $storedPath,
                    'sort_order' => $nextSort + $index,
                    'is_primary' => ! $hasPrimary && $index === 0,
                ]);

                $hasPrimary = true;
            }
        }
    }
}
