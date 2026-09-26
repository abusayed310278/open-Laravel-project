<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FILE EXISTENCE CHECK ON LOCAL DISK ===" . PHP_EOL;
foreach (App\Models\ProductImage::take(10)->get() as $img) {
    $exists = Illuminate\Support\Facades\Storage::disk('public')->exists($img->path);
    echo "ID: {$img->id} | Path: {$img->path} | Local Exists: " . ($exists ? 'YES' : 'NO') . PHP_EOL;
}
