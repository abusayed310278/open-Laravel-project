<?php

use App\Jobs\CleanupExpiredCarts;
use App\Jobs\ExpireListings;
use App\Jobs\ProcessPendingPayouts;
use App\Jobs\SendSubscriptionExpiryReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:fill-missing-images', function () {
    $products = \App\Models\Product::with('images', 'category')->get();
    $count = 0;

    foreach ($products as $product) {
        if ($product->images->isEmpty()) {
            $product->images()->create([
                'path' => $product->defaultPlaceholderImage(),
                'type' => 'gallery',
                'sort_order' => 0,
                'is_primary' => true,
            ]);
            $count++;
            $this->info("Attached image for product #{$product->id}: {$product->title}");
        }
    }

    $this->info("Completed. Added images to {$count} product(s).");
})->purpose('Ensure every product in the database has at least one valid image');

Schedule::job(new ExpireListings)->daily();
Schedule::job(new SendSubscriptionExpiryReminders)->daily();
Schedule::job(new ProcessPendingPayouts)->daily();
Schedule::job(new CleanupExpiredCarts)->weekly();
