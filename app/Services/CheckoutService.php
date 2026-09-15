<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorOrder;
use App\Notifications\OrderPlaced;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PaymentService $paymentService,
        private readonly SettingsService $settingsService,
        private readonly InvoiceService $invoiceService,
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * Payment methods usable for the whole order: the intersection of what
     * every seller group in the cart accepts. Stripe/PayPal are left out
     * entirely until real gateway credentials make them functional — only
     * Cash on Delivery and Manual Bank Transfer can actually settle today.
     *
     * @return array<int, PaymentMethod>
     */
    public function availablePaymentMethods(Cart $cart): array
    {
        $groups = $this->cartService->groupedBySeller($cart);

        if ($groups === []) {
            return [];
        }

        $perGroup = array_map(
            fn (array $group) => array_map(fn (PaymentMethod $m) => $m->value, $this->methodsForGroup($group)),
            $groups,
        );

        $common = array_intersect(...$perGroup);

        return array_map(fn (string $value) => PaymentMethod::from($value), array_values($common));
    }

    /**
     * @return array<int, PaymentMethod>
     */
    private function methodsForGroup(array $group): array
    {
        if ($group['route'] === 'openbox') {
            $methods = [PaymentMethod::Cod];

            if (filled($this->settingsService->get('bank_details'))) {
                $methods[] = PaymentMethod::ManualBank;
            }

            return $methods;
        }

        $settings = $group['seller']->paymentSettings;
        $codEnabled = $settings ? $settings->cod_enabled : true;
        $manualBankEnabled = $settings && $settings->manual_bank_enabled && filled($settings->bank_details);

        $methods = [];

        if ($codEnabled) {
            $methods[] = PaymentMethod::Cod;
        }

        if ($manualBankEnabled) {
            $methods[] = PaymentMethod::ManualBank;
        }

        return $methods;
    }

    /**
     * Group the cart by seller, compute shipping per seller, and total the
     * whole order. No tax engine or coupon validation exists yet — both are
     * zero/no-op placeholders until those subsystems land.
     *
     * @return array{groups: array, subtotal: float, shipping: float, total: float}
     */
    public function summary(Cart $cart): array
    {
        $groups = $this->cartService->groupedBySeller($cart);

        $subtotal = array_sum(array_column($groups, 'subtotal'));
        $shipping = array_sum(array_column($groups, 'shipping'));

        return [
            'groups' => $groups,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
        ];
    }

    /**
     * Place an order from the cart. Stripe/PayPal are stubbed until real
     * gateway credentials are configured — only Cash on Delivery and manual
     * bank transfer actually settle for now, each vendor order getting a
     * pending Transaction that verification (COD collection / bank-proof
     * review) later marks Completed.
     */
    public function placeOrder(User $customer, Cart $cart, Address $shipping, Address $billing, PaymentMethod $paymentMethod): Order
    {
        abort_if($cart->items->isEmpty(), 422, 'Your cart is empty.');
        abort_unless(in_array($paymentMethod, $this->availablePaymentMethods($cart), true), 422, 'That payment method is not available for this order.');

        return DB::transaction(function () use ($customer, $cart, $shipping, $billing, $paymentMethod) {
            $summary = $this->summary($cart);

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'customer_id' => $customer->id,
                'billing_address_id' => $billing->id,
                'shipping_address_id' => $shipping->id,
                'status' => OrderStatus::Confirmed,
                'subtotal' => $summary['subtotal'],
                'shipping_total' => $summary['shipping'],
                'tax_total' => 0,
                'total' => $summary['total'],
            ]);

            foreach ($summary['groups'] as $group) {
                $vendorOrder = VendorOrder::create([
                    'order_id' => $order->id,
                    'vendor_id' => $group['seller']->id,
                    'vendor_order_number' => $this->nextVendorOrderNumber(),
                    'status' => OrderStatus::Confirmed,
                    'subtotal' => $group['subtotal'],
                    'shipping' => $group['shipping'],
                    'tax' => 0,
                    'total' => $group['subtotal'] + $group['shipping'],
                    'payment_route' => $group['route'],
                    'payment_method' => $paymentMethod,
                ]);

                $this->paymentService->recordPendingTransaction($vendorOrder, $paymentMethod->value);

                /** @var CartItem $item */
                foreach ($group['items'] as $item) {
                    $orderItem = $order->items()->create([
                        'vendor_order_id' => $vendorOrder->id,
                        'product_id' => $item->product_id,
                        'variant_id' => $item->variant_id,
                        'product_title' => $item->product->title,
                        'product_grade' => $item->product->isVerified() ? $item->product->grade->value : null,
                        'product_condition' => $item->product->condition->value,
                        'sku' => $item->product->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->price,
                        'total_price' => $item->price * $item->quantity,
                        'payment_route' => $item->payment_route,
                    ]);

                    $this->inventoryService->deduct($item->product, $item->quantity, InventoryMovementType::Sale, $orderItem);
                }

                $vendorOrder->statusHistories()->create([
                    'order_id' => $order->id,
                    'status' => OrderStatus::Confirmed->value,
                    'created_by' => $customer->id,
                ]);

                $this->invoiceService->generateForVendorOrder($vendorOrder);

                $group['seller']->notify(new OrderPlaced($order, $vendorOrder));
            }

            $order->statusHistories()->create([
                'status' => OrderStatus::Confirmed->value,
                'created_by' => $customer->id,
            ]);

            $customer->notify(new OrderPlaced($order));

            $cart->items()->delete();

            ActivityLog::record('order.placed', $order, ['total' => $summary['total']]);

            return $order->fresh(['vendorOrders.items', 'items']);
        });
    }

    private function nextOrderNumber(): string
    {
        $year = now()->format('Y');
        $count = Order::query()->whereYear('created_at', now()->year)->count() + 1;

        return "ORD-{$year}-".str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }

    private function nextVendorOrderNumber(): string
    {
        $year = now()->format('Y');
        $count = VendorOrder::query()->whereYear('created_at', now()->year)->count() + 1;

        return "VO-{$year}-".str_pad((string) $count, 6, '0', STR_PAD_LEFT);
    }
}
