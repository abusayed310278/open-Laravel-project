<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProductReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductReportRequest;
use App\Models\Product;
use App\Models\ProductReport;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProductReportController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function store(StoreProductReportRequest $request, string|int $product): JsonResponse
    {
        $productModel = is_numeric($product)
            ? Product::findOrFail((int) $product)
            : Product::where('slug', $product)->orWhere('id', $product)->firstOrFail();

        $user = Auth::user();

        if ($productModel->user_id === $user->id) {
            return response()->json(['message' => 'You cannot report your own product.'], 422);
        }

        $existing = ProductReport::query()
            ->where('product_id', $productModel->id)
            ->where('reporter_id', $user->id)
            ->where('status', ProductReportStatus::Pending)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already submitted a report for this product. Our team is reviewing it.',
            ], 200);
        }

        $report = $this->productService->report(
            $productModel,
            $user,
            $request->string('reason')->value(),
            $request->filled('details') ? $request->string('details')->value() : null,
        );

        return response()->json([
            'message' => 'Thank you for your report. Our team will review this listing.',
            'report' => $report,
        ], 201);
    }
}
