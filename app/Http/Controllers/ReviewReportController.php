<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewReportRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReviewReportController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function store(StoreReviewReportRequest $request, Review $review): RedirectResponse
    {
        $this->reviews->report($review, Auth::user(), $request->string('reason')->value());

        return back()->with('status', 'Thanks — we\'ll take a look at this review.');
    }
}
