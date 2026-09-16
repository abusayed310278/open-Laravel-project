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
            ->with(['reviewer', 'images'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->simplePaginate(10)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statuses' => ReviewStatus::cases(),
        ]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'status' => ['required', \Illuminate\Validation\Rule::enum(ReviewStatus::class)],
        ]);

        $review->update($validated);

        return back()->with('status', 'Review updated successfully.');
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

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return back()->with('status', 'Review deleted.');
    }
}
