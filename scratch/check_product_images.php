<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::with('images')->get();

echo "Total Products: " . $products->count() . "\n";

$noImages = 0;
$brokenImages = [];

foreach ($products as $p) {
    $imgUrl = $p->primaryImageUrl();
    $hasImageRelation = $p->images->count() > 0;
    
    if (!$hasImageRelation) {
        $noImages++;
        echo "Product ID {$p->id} '{$p->title}' has NO images relation! Fallback URL: {$imgUrl}\n";
    } else {
        foreach ($p->images as $img) {
            $url = $img->url();
            // check if path starts with storage and file exists or http
            if (str_starts_with($img->path, 'http')) {
                // external
            } else {
                $storagePath = storage_path('app/public/' . ltrim($img->path, '/'));
                if (!file_exists($storagePath)) {
                    $brokenImages[] = "Product ID {$p->id} image ID {$img->id} path '{$img->path}' file missing at '{$storagePath}'";
                }
            }
        }
    }
}

echo "\nProducts with NO images relation: {$noImages}\n";
echo "Broken storage file references: " . count($brokenImages) . "\n";
foreach ($brokenImages as $b) {
    echo " -> {$b}\n";
}
