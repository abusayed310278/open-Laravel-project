<?php

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Queue::fake();
});

test('checkout deducts stock and logs a sale movement', function () {
    $product = Product::factory()->live()->create(['quantity' => 5, 'price' => 100]);
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 2);

    $order = app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Cod);

    expect($order)->toBeInstanceOf(Order::class);
    expect($product->fresh()->quantity)->toBe(3);

    $movement = InventoryMovement::query()->where('product_id', $product->id)->first();
    expect($movement->type->value)->toBe('sale');
    expect($movement->quantity)->toBe(-2);
});

test('checkout rejects an order that exceeds available stock and changes nothing', function () {
    $product = Product::factory()->live()->create(['quantity' => 1, 'price' => 100]);
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 2);

    expect(fn () => app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Cod))
        ->toThrow(HttpException::class);

    expect($product->fresh()->quantity)->toBe(1);
    expect(Order::query()->count())->toBe(0);
    expect(InventoryMovement::query()->count())->toBe(0);
});

test('checkout rejects an unavailable payment method', function () {
    $product = Product::factory()->live()->create(['quantity' => 5]);
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 1);

    // Seller-route items default to COD-only (no VendorPaymentSetting row),
    // so Stripe should never be an option yet.
    expect(fn () => app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Stripe))
        ->toThrow(HttpException::class);
});
