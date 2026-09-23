<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$total = Product::count();
$live = Product::live()->count();

$byPublicationStatus = Product::query()
    ->selectRaw('publication_status, count(*) as total')
    ->groupBy('publication_status')
    ->pluck('total', 'publication_status')
    ->toArray();

$byApprovalStatus = Product::query()
    ->selectRaw('approval_status, count(*) as total')
    ->groupBy('approval_status')
    ->pluck('total', 'approval_status')
    ->toArray();

echo json_encode([
    'total_products' => $total,
    'live_on_website' => $live,
    'publication_status_breakdown' => $byPublicationStatus,
    'approval_status_breakdown' => $byApprovalStatus,
], JSON_PRETTY_PRINT);
