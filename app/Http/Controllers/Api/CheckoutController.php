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
            $address = Address::where('user_id', $user->id)->findOrFail($validated['address_id']);
        } else {
            $address = Address::create([
                'user_id' => $user->id,
                'name' => $validated['address']['name'],
                'phone' => $validated['address']['phone'],
                'address_line_1' => $validated['address']['address_line_1'],
                'city' => $validated['address']['city'],
                'state' => $validated['address']['state'],
                'country' => $validated['address']['country'],
                'postal_code' => $validated['address']['postal_code'],
            ]);
        }

        $paymentMethod = PaymentMethod::from($validated['payment_method']);
        $order = $this->checkout->placeOrder($user, $address, $address, $paymentMethod);

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => new OrderResource($order->load('vendorOrders.items', 'vendorOrders.vendor', 'shippingAddress')),
        ], 201);
    }
}
