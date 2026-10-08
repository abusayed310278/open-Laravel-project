<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CompareController extends Controller
{
    public const MAX_PRODUCTS = 4;

    /**
     * Shareable comparison page: /compare?ids=1,2,3 (order preserved, max 4).
     */
    public function index(Request $request): View
    {
        $ids = collect(explode(',', $request->string('ids')->value()))
            ->map(fn (string $id) => (int) trim($id))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->take(self::MAX_PRODUCTS)
            ->values();

        /** @var Collection<int, Product> $products */
        $products = $ids->isEmpty()
            ? collect()
            : Product::query()
                ->live()
                ->with(['images', 'category', 'brand', 'user.businessProfile', 'user.salerProfile'])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn (Product $product) => $ids->search($product->id))
                ->values();

        return view('pages.compare', [
            'products' => $products,
            'maxProducts' => self::MAX_PRODUCTS,
        ]);
    }
}
