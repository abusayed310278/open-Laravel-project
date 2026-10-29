<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\ActivityLog;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Transaction;
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
     * Active payment methods enabled on the central Admin platform gateway.
     */
    public function platformMethods(): array
    {
        $settings = $this->settingsService;

        $platformStripeEnabled = ($settings->get('stripe_enabled') === '1' || filled(config('services.stripe.secret')))
            && ($settings->has('stripe_secret_key') || filled(config('services.stripe.secret')));

        $platformPaypalEnabled = ($settings->get('paypal_enabled') === '1' || filled(config('services.paypal.client_secret')))
            && ($settings->has('paypal_client_secret') || filled(config('services.paypal.client_secret')));

        $platformBankEnabled = filled($settings->get('bank_details'));

        $methods = [PaymentMethod::Cod];

        if ($platformStripeEnabled) {
            $methods[] = PaymentMethod::Stripe;
        }

        if ($platformPaypalEnabled) {
            $methods[] = PaymentMethod::Paypal;
        }

        if ($platformBankEnabled) {
            $methods[] = PaymentMethod::ManualBank;
        }

        return $methods;
    }

    /**
     * Determine which payment methods are offered at checkout.
     *
     * - Multi-vendor cart (items from 2+ sellers): Customer always pays through
     *   the central Admin Platform Gateway. Platform settles seller wallets.
     * - Single-vendor cart (items from 1 seller): Show that seller's active
     *   gateways (from VendorPaymentSetting). If none configured, fall back to
     *   Admin platform gateway.
     *
     * @return array<int, PaymentMethod>
     */
    public function availablePaymentMethods(Cart $cart): array
    {
        $groups = $this->cartService->groupedBySeller($cart);

        if ($groups === []) {
            return $this->platformMethods();
        }

        // Multi-vendor cart: route through Admin Platform Gateway
        if (count($groups) > 1) {
            return $this->platformMethods();
        }

        // Single-vendor cart
        return $this->methodsForGroup($groups[0]);
    }

    /**
     * @return array<int, PaymentMethod>
     */
    public function methodsForSeller(?\App\Models\User $seller): array
    {
        $platformMethods = $this->platformMethods();

        if (! $seller || $seller->isAdmin()) {
            return $platformMethods;
        }

        $settings = $seller->paymentSettings;
        if (! $settings) {
            return $platformMethods;
        }

        $vendorMethods = [];
        if ($settings->cod_enabled) {
            $vendorMethods[] = PaymentMethod::Cod;
        }
        if ($settings->stripe_enabled && $settings->hasStripeConnected()) {
            $vendorMethods[] = PaymentMethod::Stripe;
        }
        if ($settings->paypal_enabled && $settings->hasPaypalConnected()) {
            $vendorMethods[] = PaymentMethod::Paypal;
        }
        if ($settings->manual_bank_enabled && filled($settings->bank_details)) {
            $vendorMethods[] = PaymentMethod::ManualBank;
        }

        if ($vendorMethods !== []) {
            return array_values(array_unique($vendorMethods, SORT_REGULAR));
        }

        return $platformMethods;
    }

    /**
     * @return array<int, PaymentMethod>
     */
    private function methodsForGroup(array $group): array
    {
        if ($group['route'] === 'openbox' || $group['seller']->isAdmin()) {
            return $this->platformMethods();
        }

        return $this->methodsForSeller($group['seller']);
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

    /**
     * Restore items from an order back into the customer's cart.
     */
    public function restoreCartFromOrder(Order $order, ?Cart $cart = null): Cart
    {
        $user = $order->customer;
        $cart = $cart ?: ($user ? $this->cartService->forUser($user) : $this->cartService->forGuest($this->cartService->sessionId()));

        $order->loadMissing('items.product');

        foreach ($order->items as $orderItem) {
            $cartItem = $cart->items()->firstOrNew([
                'product_id' => $orderItem->product_id,
                'variant_id' => $orderItem->variant_id,
            ]);

            $cartItem->quantity = ($cartItem->exists ? $cartItem->quantity : 0) + $orderItem->quantity;
            $cartItem->price = $orderItem->unit_price;
            $cartItem->payment_route = $orderItem->payment_route ?? 'cod';
            $cartItem->save();
        }

        return $cart;
    }

    /**
     * Cancel an order whose online payment was never completed and restore deducted inventory.
     */
    public function cancelUnconfirmedOrder(Order $order, ?User $actor = null, string $reason = 'Payment was cancelled by customer'): void
    {
        if ($order->status === OrderStatus::Cancelled) {
            return;
        }

        DB::transaction(function () use ($order, $actor, $reason) {
            $safeReason = \Illuminate\Support\Str::limit($reason, 250, '...');
            $order->loadMissing('vendorOrders.items.product');

            foreach ($order->vendorOrders as $vendorOrder) {
                if ($vendorOrder->status !== OrderStatus::Cancelled) {
                    foreach ($vendorOrder->items as $item) {
                        if ($item->product) {
                            $this->inventoryService->restore(
                                $item->product,
                                $item->quantity,
                                InventoryMovementType::Return,
                                $item,
                                $actor,
                                $safeReason
                            );
                        }
                    }

                    $vendorOrder->update(['status' => OrderStatus::Cancelled]);
                    $vendorOrder->statusHistories()->create([
                        'order_id' => $order->id,
                        'status' => OrderStatus::Cancelled->value,
                        'created_by' => $actor?->id,
                        'notes' => $safeReason,
                    ]);
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);
            $order->statusHistories()->create([
                'status' => OrderStatus::Cancelled->value,
                'created_by' => $actor?->id,
                'notes' => $safeReason,
            ]);

            Transaction::where('order_id', $order->id)
                ->whereIn('status', [TransactionStatus::Pending, TransactionStatus::Completed])
                ->whereNull('provider_transaction_id')
                ->update(['status' => TransactionStatus::Failed]);
        });
    }

    /**
     * Automatically detect and recover cart if the customer returns from an unconfirmed payment portal.
     */
    public function recoverPendingPaymentCart(?User $user, ?int $pendingOrderId = null): ?Order
    {
        $cart = $user ? $this->cartService->forUser($user) : $this->cartService->forGuest($this->cartService->sessionId());

        // Only recover if the cart is currently empty
        if ($cart->items()->exists()) {
            return null;
        }

        $pendingOrder = null;

        if ($pendingOrderId) {
            $pendingOrder = Order::with('items.product', 'vendorOrders.items')->find($pendingOrderId);
        } elseif ($user) {
            $pendingOrder = Order::with('items.product', 'vendorOrders.items')
                ->where('customer_id', $user->id)
                ->where('created_at', '>=', now()->subHours(2))
                ->whereHas('vendorOrders', fn ($q) => $q->whereIn('payment_method', ['stripe', 'paypal']))
                ->whereNotIn('status', [OrderStatus::Delivered, OrderStatus::Shipped, OrderStatus::Cancelled])
                ->whereDoesntHave('transactions', fn ($q) => $q->where('status', TransactionStatus::Completed)->whereNotNull('provider_transaction_id'))
                ->latest()
                ->first();
        }

        if (! $pendingOrder || $pendingOrder->status === OrderStatus::Cancelled) {
            return null;
        }

        if ($user && $pendingOrder->customer_id !== $user->id) {
            return null;
        }

        $this->restoreCartFromOrder($pendingOrder, $cart);
        $this->cancelUnconfirmedOrder($pendingOrder, $user ?? $pendingOrder->customer, 'Customer returned to checkout without completing payment.');

        return $pendingOrder;
    }

    private function nextOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "ORD-{$year}-";

        $latest = Order::query()
            ->where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('order_number');

        $seq = 0;
        if ($latest && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $latest, $m)) {
            $seq = (int) $m[1];
        }

        do {
            $seq++;
            $candidate = "{$prefix}" . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $candidate)->exists());

        return $candidate;
    }

    private function nextVendorOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "VO-{$year}-";

        $latest = VendorOrder::query()
            ->where('vendor_order_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('vendor_order_number');

        $seq = 0;
        if ($latest && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $latest, $m)) {
            $seq = (int) $m[1];
        }

        do {
            $seq++;
            $candidate = "{$prefix}" . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        } while (VendorOrder::where('vendor_order_number', $candidate)->exists());

        return $candidate;
    }
}
