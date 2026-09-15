<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewReportStatus;
use App\Http\Controllers\Controller;
use App\Models\ReviewReport;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewReportController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $reports = ReviewReport::query()
            ->with('review.reviewer', 'reporter')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.review-reports.index', [
            'reports' => $reports,
            'statuses' => ReviewReportStatus::cases(),
        ]);
    }

    public function resolve(ReviewReport $report): RedirectResponse
    {
        $this->reviews->resolveReport($report);

        return back()->with('status', 'Report resolved.');
    }

    public function dismiss(ReviewReport $report): RedirectResponse
    {
        $this->reviews->dismissReport($report);

        return back()->with('status', 'Report dismissed.');
    }
}
