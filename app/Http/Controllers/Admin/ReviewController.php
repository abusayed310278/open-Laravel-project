<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with('reviewer')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statuses' => ReviewStatus::cases(),
        ]);
    }

    public function approve(Request $request, Review $review): RedirectResponse
    {
        $this->reviews->approve($review, $request->user());

        return back()->with('status', 'Review approved.');
    }

    public function reject(Request $request, Review $review): RedirectResponse
    {
        $this->reviews->reject($review, $request->user());

        return back()->with('status', 'Review rejected.');
    }
}
