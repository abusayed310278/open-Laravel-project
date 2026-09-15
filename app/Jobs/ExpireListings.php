<?php

namespace App\Jobs;

use App\Enums\ProductPublicationStatus;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Daily sweep: any published listing whose expires_at (set from the
 * seller's subscription/credit expiry) has passed drops off the storefront.
 */
class ExpireListings implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Product::query()
            ->where('publication_status', ProductPublicationStatus::Published)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => ProductStatus::Expired,
                'publication_status' => ProductPublicationStatus::Expired,
            ]);
    }
}
