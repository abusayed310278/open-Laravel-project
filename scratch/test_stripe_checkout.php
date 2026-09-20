<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\SettingsService;
use App\Enums\PaymentMethod;

$settings = app(SettingsService::class);
echo "Current stripe_enabled: " . var_export($settings->get('stripe_enabled'), true) . PHP_EOL;

// Enable stripe in settings service
$settings->set('stripe_enabled', '1', 'payments');
$settings->set('stripe_publishable_key', 'pk_test_sample123', 'payments');
$settings->set('stripe_secret_key', 'sk_test_sample123', 'payments');

echo "Updated stripe_enabled: " . var_export($settings->get('stripe_enabled'), true) . PHP_EOL;

$customer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);
$cart = app(CartService::class)->forUser($customer);

$available = app(CheckoutService::class)->availablePaymentMethods($cart);
echo "Available Payment Methods: " . implode(', ', array_map(fn($m) => $m->value, $available)) . PHP_EOL;
