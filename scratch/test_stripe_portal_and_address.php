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

$customer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);

// Check customer address count
echo "Customer addresses count: " . $customer->addresses()->count() . PHP_EOL;

if ($customer->addresses()->count() === 0) {
    Address::create([
        'user_id' => $customer->id,
        'name' => $customer->name,
        'phone' => '1234567890',
        'line1' => '100 Default Way',
        'city' => 'Boston',
        'state' => 'MA',
        'country' => 'United States',
        'postal_code' => '02108',
        'is_default' => true,
    ]);
    echo "Created default address." . PHP_EOL;
}

$firstAddress = $customer->addresses()->orderByDesc('is_default')->first();
echo "Default Address ID: " . $firstAddress->id . " (" . $firstAddress->name . ")" . PHP_EOL;

// Verify Stripe Portal Route exists
$routePortal = route('checkout.stripe-portal', ['order' => 1]);
echo "Stripe Portal Route: " . $routePortal . PHP_EOL;

