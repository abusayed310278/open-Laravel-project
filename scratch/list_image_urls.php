<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::with('images')->get();

foreach ($products as $p) {
    echo "ID: {$p->id} | Title: {$p->title} | PrimaryImageURL: " . $p->primaryImageUrl() . "\n";
}
