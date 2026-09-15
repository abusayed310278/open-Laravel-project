<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ReviewableType;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderFulfillmentService;
use App\Services\ReviewService;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Queue::fake();
});

function deliverAnOrder(Product $product, User $customer): Order
{
    $address = Address::factory()->create(['user_id' => $customer->id]);
    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 1);

    $order = app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Cod);
    $vendorOrder = $order->vendorOrders->first();
    $seller = $vendorOrder->vendor;

    foreach ([OrderStatus::Processing, OrderStatus::Packed, OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
        app(OrderFulfillmentService::class)->updateStatus($vendorOrder->fresh(), $status, $seller);
    }

    return $order->fresh();
}

test('a product is not reviewable until its order is delivered', function () {
    $product = Product::factory()->live()->create(['quantity' => 5]);
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 1);
    $order = app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Cod);

    $eligible = app(ReviewService::class)->eligibleReviewables($customer, $order->fresh());

    expect($eligible)->toBeEmpty();
});

test('a delivered order becomes reviewable for both the product and the seller', function () {
    $product = Product::factory()->live()->create(['quantity' => 5]);
    $customer = User::factory()->create();

    $order = deliverAnOrder($product, $customer);

    $eligible = app(ReviewService::class)->eligibleReviewables($customer, $order);

    expect($eligible)->toHaveCount(2);
    expect(collect($eligible)->pluck('type')->map(fn ($t) => $t->value)->all())
        ->toEqualCanonicalizing(['product', 'seller']);
});

test('the same order/product cannot be reviewed twice', function () {
    $product = Product::factory()->live()->create(['quantity' => 5]);
    $customer = User::factory()->create();

    $order = deliverAnOrder($product, $customer);

    app(ReviewService::class)->create($customer, $order, ReviewableType::Product, $product->id, 5, 'Great', 'Loved it');

    expect(fn () => app(ReviewService::class)->create($customer, $order, ReviewableType::Product, $product->id, 4, 'Again', 'Trying twice'))
        ->toThrow(HttpException::class);
});
