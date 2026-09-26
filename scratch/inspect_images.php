<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRODUCT IMAGES ===" . PHP_EOL;
foreach (App\Models\ProductImage::all() as $img) {
    echo "ID: {$img->id} | Path: {$img->path} | URL: " . $img->url() . PHP_EOL;
}

echo PHP_EOL . "=== BRANDS ===" . PHP_EOL;
foreach (App\Models\Brand::all() as $b) {
    echo "ID: {$b->id} | Logo: {$b->logo} | URL: " . $b->logoUrl() . PHP_EOL;
}

echo PHP_EOL . "=== BANNERS ===" . PHP_EOL;
foreach (App\Models\Banner::all() as $bn) {
    echo "ID: {$bn->id} | Image: {$bn->image} | URL: " . $bn->imageUrl() . PHP_EOL;
}
