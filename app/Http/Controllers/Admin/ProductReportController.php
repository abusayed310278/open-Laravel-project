<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductReportStatus;
use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReportController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    public function index(Request $request): View
    {
        $reports = ProductReport::query()
            ->with(['product.user', 'reporter'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.product-reports.index', [
            'reports' => $reports,
            'statuses' => ProductReportStatus::cases(),
        ]);
    }

    public function resolve(ProductReport $report): RedirectResponse
    {
        $this->productService->resolveReport($report);

        return back()->with('status', 'Product report marked as resolved.');
    }

    public function dismiss(ProductReport $report): RedirectResponse
    {
        $this->productService->dismissReport($report);

        return back()->with('status', 'Product report dismissed.');
    }
}
