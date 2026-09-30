<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {}

    public function availablePaymentMethods(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $cartModel = $this->cart->forUser($user);
        if ($cartModel->items()->count() === 0 && $request->filled('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $it) {
                $pid = $it['product_id'] ?? null;
                $qty = (int) ($it['quantity'] ?? 1);
                if ($pid && ($prod = \App\Models\Product::find($pid))) {
                    $this->cart->add($user, $prod, $qty);
                }
            }
            $cartModel = $this->cart->forUser($user);
        }
            $available = $this->checkout->availablePaymentMethods($cartModel);
            $availableValues = array_map(fn (PaymentMethod $m) => $m->value, $available);

            $allMethods = [
                [
                    'id' => 'cod',
                    'name' => 'Cash on Delivery',
                    'enabled' => in_array('cod', $availableValues, true),
                ],
                [
                    'id' => 'manual_bank',
                    'name' => 'Manual Bank Transfer',
                    'enabled' => in_array('manual_bank', $availableValues, true),
                ],
                [
                    'id' => 'stripe',
                    'name' => 'Card (Stripe)',
                    'enabled' => in_array('stripe', $availableValues, true),
                ],
                [
                    'id' => 'paypal',
                    'name' => 'PayPal',
                    'enabled' => in_array('paypal', $availableValues, true),
                ],
            ];

            return response()->json([
                'payment_methods' => $allMethods,
            ]);
        }

        return response()->json([
            'payment_methods' => [
                ['id' => 'cod', 'name' => 'Cash on Delivery', 'enabled' => true],
                ['id' => 'manual_bank', 'name' => 'Manual Bank Transfer', 'enabled' => true],
                ['id' => 'stripe', 'name' => 'Card (Stripe)', 'enabled' => true],
                ['id' => 'paypal', 'name' => 'PayPal', 'enabled' => true],
            ],
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['nullable'],
            'address' => ['nullable', 'array'],
            'address.name' => ['nullable', 'string'],
            'address.phone' => ['nullable', 'string'],
            'address.line1' => ['nullable', 'string'],
            'address.address_line_1' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.country' => ['nullable', 'string'],
            'address.postal_code' => ['nullable', 'string'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        $user = $request->user();

        $address = null;
        if (! empty($validated['address_id']) && is_numeric($validated['address_id'])) {
            $address = $user->addresses()->where('id', (int) $validated['address_id'])->first();
        }

        if (! $address && ! empty($validated['address'])) {
            $addr = $validated['address'];
            $line1 = $addr['line1'] ?? $addr['address_line_1'] ?? 'Zone 45, Street 12, Villa 7';
            $address = $user->addresses()->firstOrCreate(
                [
                    'name' => $addr['name'] ?? $user->name,
                    'line1' => $line1,
                    'city' => $addr['city'] ?? 'Doha',
                ],
                [
                    'phone' => $addr['phone'] ?? $user->phone ?? '+974 5555 1234',
                    'state' => $addr['state'] ?? 'Doha',
                    'country' => $addr['country'] ?? 'Qatar',
                    'postal_code' => $addr['postal_code'] ?? '00000',
                ]
            );
        }

        if (! $address) {
            $address = $user->addresses()->first() ?? $user->addresses()->create([
                'name' => $user->name,
                'phone' => $user->phone ?? '+974 5555 1234',
                'line1' => 'Zone 45, Street 12, Villa 7',
                'city' => 'Doha',
                'state' => 'Doha',
                'country' => 'Qatar',
                'postal_code' => '00000',
                'is_default' => true,
            ]);
        }

        $cartModel = $this->cart->forUser($user);
        if ($cartModel->items()->count() === 0 && $request->filled('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $it) {
                $pid = $it['product_id'] ?? null;
                $qty = (int) ($it['quantity'] ?? 1);
                if ($pid && ($prod = \App\Models\Product::find($pid))) {
                    $this->cart->add($user, $prod, $qty);
                }
            }
            $cartModel = $this->cart->forUser($user);
        }
        $paymentMethod = PaymentMethod::from($validated['payment_method']);
        $order = $this->checkout->placeOrder($user, $cartModel, $address, $address, $paymentMethod);

        $paymentUrl = null;
        $orderToken = sha1($order->id . $order->order_number . config('app.key'));

        if ($paymentMethod === PaymentMethod::Stripe) {
            $firstVendor = $order->vendorOrders()->with('vendor.paymentSettings')->first()?->vendor;
            $vendorStripeKey = $firstVendor?->paymentSettings?->stripe_secret_key;
            $secretKey = filled($vendorStripeKey)
                ? $vendorStripeKey
                : (app(SettingsService::class)->getDecrypted('stripe_secret_key') ?: config('services.stripe.secret'));

            if (filled($secretKey)) {
                try {
                    $response = Http::withBasicAuth($secretKey, '')
                        ->asForm()
                        ->post('https://api.stripe.com/v1/checkout/sessions', [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => 'usd',
                                        'product_data' => [
                                            'name' => 'Order #' . $order->order_number,
                                        ],
                                        'unit_amount' => (int) round($order->total * 100),
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'success_url' => url('/checkout/stripe-success/' . $order->id) . '?token=' . $orderToken . '&session_id={CHECKOUT_SESSION_ID}',
                            'cancel_url' => url('/checkout/stripe-portal/' . $order->id) . '?token=' . $orderToken,
                        ]);

                    if ($response->successful() && isset($response->json()['url'])) {
                        $paymentUrl = $response->json()['url'];
                    }
                } catch (\Throwable $e) {
                    Log::warning('Stripe API session creation failed: ' . $e->getMessage());
                }
            }

            if (! $paymentUrl) {
                $paymentUrl = url('/checkout/stripe-portal/' . $order->id . '?token=' . $orderToken);
            }
        } elseif ($paymentMethod === PaymentMethod::Paypal) {
            $paymentUrl = url('/checkout/stripe-portal/' . $order->id . '?token=' . $orderToken . '&method=paypal');
        }

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => new OrderResource($order->load('vendorOrders.items', 'vendorOrders.vendor', 'shippingAddress')),
            'payment_url' => $paymentUrl,
        ], 201);
    }
}