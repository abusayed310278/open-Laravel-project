<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\SellerPayoutMethod;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\CommissionRule;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderFulfillmentService;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Queue::fake();
});

test('a sale credits pending balance, delivery releases it to available', function () {
    CommissionRule::create([
        'type' => 'global',
        'commission_type' => 'percentage',
        'value' => 10,
        'priority' => 0,
        'is_active' => true,
    ]);

    $seller = User::factory()->role(UserRole::Business)->create();
    $product = Product::factory()->live()->create(['user_id' => $seller->id, 'price' => 100, 'quantity' => 5]);
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 1);

    $order = app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Cod);
    $vendorOrder = $order->vendorOrders->first();

    app(PaymentService::class)->confirmCodCollected($vendorOrder);

    $wallet = app(WalletService::class)->walletFor($seller);
    expect((float) $wallet->pending_balance)->toBe(90.0);
    expect((float) $wallet->available_balance)->toBe(0.0);

    app(OrderFulfillmentService::class)->updateStatus($vendorOrder->fresh(), OrderStatus::Processing, $seller);
    app(OrderFulfillmentService::class)->updateStatus($vendorOrder->fresh(), OrderStatus::Packed, $seller);
    app(OrderFulfillmentService::class)->updateStatus($vendorOrder->fresh(), OrderStatus::Shipped, $seller);
    app(OrderFulfillmentService::class)->updateStatus($vendorOrder->fresh(), OrderStatus::Delivered, $seller);

    $wallet->refresh();
    expect((float) $wallet->pending_balance)->toBe(0.0);
    expect((float) $wallet->available_balance)->toBe(90.0);
});

test('an approved refund reverses the seller earnings and total_earned', function () {
    $seller = User::factory()->role(UserRole::Business)->create();
    $product = Product::factory()->live()->create(['user_id' => $seller->id, 'price' => 100, 'quantity' => 5]);
    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);

    $cart = app(CartService::class)->forUser($customer);
    app(CartService::class)->add($cart, $product, 1);

    $order = app(CheckoutService::class)->placeOrder($customer, $cart->fresh(), $address, $address, PaymentMethod::Cod);
    $vendorOrder = $order->vendorOrders->first();

    app(PaymentService::class)->confirmCodCollected($vendorOrder);

    $wallet = app(WalletService::class)->walletFor($seller);
    expect((float) $wallet->pending_balance)->toBe(100.0); // no commission rule = 0% taken

    $refund = app(PaymentService::class)->requestRefund($vendorOrder->fresh(), $customer, 'Not as described', 100);
    app(PaymentService::class)->approveRefund($refund, $seller);

    $wallet->refresh();
    expect((float) $wallet->pending_balance)->toBe(0.0);
    expect((float) $wallet->total_earned)->toBe(0.0);
});

test('a payout request reserves available balance and completion records total_withdrawn', function () {
    $seller = User::factory()->create();
    $wallet = app(WalletService::class)->walletFor($seller);
    $wallet->update(['available_balance' => 200]);

    $payout = app(WalletService::class)->requestPayout($seller, 150, SellerPayoutMethod::ManualBank);

    expect((float) $wallet->fresh()->available_balance)->toBe(50.0);

    app(WalletService::class)->approvePayout($payout, $seller);
    app(WalletService::class)->markProcessing($payout);
    app(WalletService::class)->completePayout($payout);

    expect((float) $wallet->fresh()->total_withdrawn)->toBe(150.0);
    expect($payout->fresh()->status->value)->toBe('completed');
});

test('requesting a payout larger than the available balance is rejected', function () {
    $seller = User::factory()->create();
    app(WalletService::class)->walletFor($seller)->update(['available_balance' => 50]);

    expect(fn () => app(WalletService::class)->requestPayout($seller, 100, SellerPayoutMethod::ManualBank))
        ->toThrow(HttpException::class);
});
