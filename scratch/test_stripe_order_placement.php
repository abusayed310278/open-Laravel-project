<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\SettingsService;
use App\Enums\PaymentMethod;

$settings = app(SettingsService::class);
$settings->set('stripe_enabled', '1', 'payments');
$settings->set('stripe_publishable_key', 'pk_test_sample123', 'payments');

$customer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);
$product = Product::live()->first();

if (!$product) {
    echo "No product found." . PHP_EOL;
    exit;
}

$cartService = app(CartService::class);
$cart = $cartService->forUser($customer);
$cartService->add($cart, $product, 1);

$address = $customer->addresses()->first() ?? Address::create([
    'user_id' => $customer->id,
    'name' => $customer->name,
    'phone' => '1234567890',
    'line1' => '123 Test St',
    'city' => 'Test City',
    'country' => 'United States',
]);

$checkout = app(CheckoutService::class);
$order = $checkout->placeOrder($customer, $cart, $address, $address, PaymentMethod::Stripe);

echo "Order Created Successfully!" . PHP_EOL;
echo "Order Number: " . $order->order_number . PHP_EOL;
echo "Vendor Orders Count: " . $order->vendorOrders()->count() . PHP_EOL;

foreach ($order->vendorOrders as $vo) {
    echo "Vendor Order Number: " . $vo->vendor_order_number . " | Payment Method: " . $vo->payment_method->value . PHP_EOL;
    $txn = \App\Models\Transaction::where('vendor_order_id', $vo->id)->first();
    echo "  Transaction: " . ($txn ? $txn->transaction_number . " (Status: " . $txn->status->value . ", Provider: " . $txn->provider . ")" : "None") . PHP_EOL;
}
