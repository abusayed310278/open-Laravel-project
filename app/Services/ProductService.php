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
    public function create(
        User $seller,
        array $data,
        array $images = [],
        array $attributeValues = [],
        ?int $primaryIndex = null
    ): Product {
        return DB::transaction(function () use ($seller, $data, $images, $attributeValues, $primaryIndex) {
            $product = $seller->products()->create([
                ...$data,
                'payment_route' => $this->paymentRouteFor($seller),
                'status' => ProductStatus::Draft,
                // New (sealed/unused) items are always Grade A — only used
                // and refurbished items go through physical grading.
                'grade' => ($data['condition'] ?? null) === ProductCondition::New->value ? ProductGrade::A : ProductGrade::Ungraded,
            ]);

            $this->syncAttributeValues($product, $attributeValues);
            $this->addImages($product, $images, $primaryIndex);
            $this->ensureImages($product);

            return $product->fresh(['images', 'attributeValues']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $images
     * @param  array<string, mixed>  $attributeValues
     * @param  array<int, int>  $deleteImageIds
     */
    public function update(
        Product $product,
        array $data,
        array $images = [],
        array $attributeValues = [],
        ?User $actor = null,
        ?int $primaryImageId = null,
        ?int $primaryImageIndex = null,
        array $deleteImageIds = []
    ): Product {
        return DB::transaction(function () use ($product, $data, $images, $attributeValues, $actor, $primaryImageId, $primaryImageIndex, $deleteImageIds) {
            $previousQuantity = $product->quantity;

            $product->update($data);

            if ($actor && array_key_exists('quantity', $data)) {
                $this->inventoryService->logAdjustment($product, $product->quantity - $previousQuantity, $actor, 'Manual edit');
            }

            // 1. Delete requested existing images
            if (! empty($deleteImageIds)) {
                $imagesToDelete = $product->images()->whereIn('id', $deleteImageIds)->get();
                foreach ($imagesToDelete as $img) {
                    if ($img->path && ! str_starts_with($img->path, 'http')) {
                        Storage::disk('public')->delete($img->path);
                    }
                    $img->delete();
                }
            }

            $this->syncAttributeValues($product, $attributeValues);

            // 2. Upload and add newly attached images
            $newPrimaryAdded = $this->addImages($product, $images, $primaryImageIndex);

            // 3. Update primary image if explicitly set on an existing image and no new image override
            if ($primaryImageId && ! $newPrimaryAdded) {
                $product->images()->update(['is_primary' => false]);
                $product->images()->where('id', $primaryImageId)->update(['is_primary' => true]);
            }

            $this->ensureImages($product);

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
    private function addImages(Product $product, array $images, ?int $primaryIndex = null): bool
    {
        if ($images === []) {
            return false;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $nextSort = (int) ($product->images()->max('sort_order') ?? 0) + 1;
        $setPrimaryInThisBatch = false;

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
                $isPrimary = ($primaryIndex !== null && (int) $primaryIndex === $index) || (! $hasPrimary && $index === 0);

                if ($isPrimary) {
                    $product->images()->update(['is_primary' => false]);
                    $hasPrimary = true;
                    $setPrimaryInThisBatch = true;
                }

                $product->images()->create([
                    'path' => $storedPath,
                    'sort_order' => $nextSort + $index,
                    'is_primary' => $isPrimary,
                ]);
            }
        }

        return $setPrimaryInThisBatch;
    }

    /**
     * Ensure that a product always has at least one valid primary image in the database.
     */
    public function ensureImages(Product $product): void
    {
        if ($product->images()->exists()) {
            return;
        }

        $imageUrl = $product->defaultPlaceholderImage();
        $product->images()->create([
            'path' => $imageUrl,
            'type' => 'gallery',
            'sort_order' => 0,
            'is_primary' => true,
        ]);
    }
}
