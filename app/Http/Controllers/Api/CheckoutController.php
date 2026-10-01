<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
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

        $stripeData = null;

        if ($paymentMethod === PaymentMethod::Stripe) {
            $firstVendor = $order->vendorOrders()->with('vendor.paymentSettings')->first()?->vendor;
            $vendorStripeKey = $firstVendor?->paymentSettings?->stripe_secret_key;
            $secretKey = filled($vendorStripeKey)
                ? $vendorStripeKey
                : (app(SettingsService::class)->getDecrypted('stripe_secret_key') ?: config('services.stripe.secret'));
            $publishableKey = config('services.stripe.key');

            if (filled($secretKey)) {
                // 1. Create Stripe PaymentIntent for native mobile Stripe PaymentSheet
                try {
                    $piResponse = Http::withBasicAuth($secretKey, '')
                        ->asForm()
                        ->post('https://api.stripe.com/v1/payment_intents', [
                            'amount' => (int) round($order->total * 100),
                            'currency' => 'usd',
                            'payment_method_types' => ['card'],
                            'description' => 'Order #' . $order->order_number,
                            'metadata' => [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                            ],
                        ]);

                    if ($piResponse->successful() && isset($piResponse->json()['client_secret'])) {
                        $stripeData = [
                            'publishable_key' => $publishableKey,
                            'client_secret' => $piResponse->json()['client_secret'],
                            'payment_intent_id' => $piResponse->json()['id'],
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                        ];
                    } else {
                        Log::info('Stripe PaymentIntent note: ' . $piResponse->body());
                    }
                } catch (\Throwable $e) {
                    Log::warning('Stripe PaymentIntent creation failed: ' . $e->getMessage());
                }

                // 2. Fallback to Stripe Checkout session if PaymentIntent was not created
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
            'stripe' => $stripeData,
        ], 201);
    }

    public function confirmStripe(Request $request, Order $order): JsonResponse
    {
        $token = $request->input('token') ?? $request->query('token');
        $validToken = sha1($order->id . $order->order_number . config('app.key'));
        $user = $request->user();

        $authorized = false;
        if ($user && $order->customer_id === $user->id) {
            $authorized = true;
        } elseif ($token && hash_equals($validToken, (string) $token)) {
            $authorized = true;
        }

        if (! $authorized) {
            return response()->json(['message' => 'Unauthorized access to order.'], 403);
        }

        foreach ($order->vendorOrders as $vendorOrder) {
            app(PaymentService::class)->confirmCodCollected($vendorOrder);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment completed via Stripe!',
            'order' => new OrderResource($order->load('vendorOrders.items', 'vendorOrders.vendor', 'shippingAddress')),
        ]);
    }
}