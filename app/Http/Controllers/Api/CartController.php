<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(Request $request): JsonResponse
    {
        $cartData = $this->cart->getCart($request->user());

        return response()->json([
            'items' => collect($cartData['items'] ?? [])->map(fn ($item) => [
                'id' => $item->id ?? null,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product' => new ProductResource($item->product),
            ]),
            'subtotal' => (float) ($cartData['subtotal'] ?? 0),
            'shipping' => (float) ($cartData['shipping'] ?? 0),
            'total' => (float) ($cartData['total'] ?? 0),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $this->cart->add($request->user(), $product, $validated['quantity']);

        return response()->json([
            'message' => 'Product added to cart',
        ]);
    }

    public function update(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::findOrFail($productId);
        $this->cart->updateQuantity($request->user(), $product, $validated['quantity']);

        return response()->json([
            'message' => 'Cart updated',
        ]);
    }

    public function remove(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $this->cart->remove($request->user(), $product);

        return response()->json([
            'message' => 'Item removed from cart',
        ]);
    }
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cart->resolveCart($request->user());
        $cart->items()->delete();

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }
}
