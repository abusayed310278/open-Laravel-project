<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\VendorOrder;
use App\Models\Invoice;

echo "Total VendorOrders: " . VendorOrder::count() . "\n";
echo "Total Invoices: " . Invoice::count() . "\n";

$saler = User::where('role', 'saler')->first();
echo "Saler: {$saler->name} (ID: {$saler->id})\n";
$salerOrders = VendorOrder::where('vendor_id', $saler->id)->with('invoice', 'items')->get();
echo "Saler Orders Count: " . $salerOrders->count() . "\n";

foreach ($salerOrders as $vOrder) {
    $invNumber = $vOrder->invoice ? $vOrder->invoice->invoice_number : 'NO INVOICE';
    $invStatus = $vOrder->invoice ? $vOrder->invoice->payment_status->value : 'N/A';
    echo " Order #{$vOrder->vendor_order_number} | Total: \${$vOrder->total} | Status: {$vOrder->status->value} | Invoice: {$invNumber} ({$invStatus})\n";
}
