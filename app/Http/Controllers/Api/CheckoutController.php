<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {}

    public function availablePaymentMethods(Request $request): JsonResponse
    {
        return response()->json([
            'payment_methods' => [
                [
                    'id' => 'cod',
                    'name' => 'Cash on Delivery',
                    'enabled' => true,
                ],
                [
                    'id' => 'manual_bank',
                    'name' => 'Manual Bank Transfer',
                    'enabled' => true,
                ],
                [
                    'id' => 'stripe',
                    'name' => 'Card (Stripe)',
                    'enabled' => true,
                ],
                [
                    'id' => 'paypal',
                    'name' => 'PayPal',
                    'enabled' => true,
                ],
            ],
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => ['nullable', 'exists:addresses,id'],
            'address' => ['nullable', 'array'],
            'address.name' => ['required_with:address', 'string'],
            'address.phone' => ['required_with:address', 'string'],
            'address.address_line_1' => ['required_with:address', 'string'],
            'address.city' => ['required_with:address', 'string'],
            'address.state' => ['required_with:address', 'string'],
            'address.country' => ['required_with:address', 'string'],
            'address.postal_code' => ['required_with:address', 'string'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
        ]);

        $user = $request->user();

        if (! empty($validated['address_id'])) {
            $address = Address::where('user_id', $user->id)->first();
            if (! $address) {
                $address = Address::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone ?? '+974 5555 1234',
                    'address_line_1' => 'Zone 45, Street 12, Villa 7',
                    'city' => 'Doha',
                    'state' => 'Doha',
                    'country' => 'Qatar',
                    'postal_code' => '00000',
                ]);
            }
        } else if (! empty($validated['address'])) {
            $address = Address::create([
                'user_id' => $user->id,
                'name' => $validated['address']['name'],
                'phone' => $validated['address']['phone'],
                'address_line_1' => $validated['address']['address_line_1'],
                'city' => $validated['address']['city'],
                'state' => $validated['address']['state'] ?? 'Doha',
                'country' => $validated['address']['country'] ?? 'Qatar',
                'postal_code' => $validated['address']['postal_code'] ?? '00000',
            ]);
        } else {
            $address = $user->addresses()->first() ?? Address::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone ?? '+974 5555 1234',
                'address_line_1' => 'Zone 45, Street 12, Villa 7',
                'city' => 'Doha',
                'state' => 'Doha',
                'country' => 'Qatar',
                'postal_code' => '00000',
            ]);
        }

        $cartModel = $this->cart->forUser($user);
        $paymentMethod = PaymentMethod::from($validated['payment_method']);
        $order = $this->checkout->placeOrder($user, $cartModel, $address, $address, $paymentMethod);

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => new OrderResource($order->load('vendorOrders.items', 'vendorOrders.vendor', 'shippingAddress')),
        ], 201);
    }
}