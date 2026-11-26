<?php

namespace App\Http\Controllers;

use App\Enums\ProductReportStatus;
use App\Http\Requests\StoreProductReportRequest;
use App\Models\Product;
use App\Models\ProductReport;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ProductReportController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function store(StoreProductReportRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($product->user_id === $user->id) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'You cannot report your own product.'], 422);
            }

            return back()->with('error', 'You cannot report your own product.');
        }

        $existing = ProductReport::query()
            ->where('product_id', $product->id)
            ->where('reporter_id', $user->id)
            ->where('status', ProductReportStatus::Pending)
            ->first();

        if ($existing) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'You have already submitted a report for this product. Our team is reviewing it.',
                ], 200);
            }

            return back()->with('status', 'You have already submitted a report for this product. Our team is reviewing it.');
        }

        $report = $this->productService->report(
            $product,
            $user,
            $request->string('reason')->value(),
            $request->filled('details') ? $request->string('details')->value() : null,
        );

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Thank you for your report. Our team will review this listing.',
                'report' => $report,
            ], 201);
        }

        return back()->with('status', 'Thank you for your report. Our team will review this listing.');
    }
}
