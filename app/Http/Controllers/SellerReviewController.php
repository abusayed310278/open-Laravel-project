<?php

namespace App\Http\Controllers;

use App\Enums\ReviewableType;
use App\Http\Requests\StoreReviewReplyRequest;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(): View
    {
        $user = Auth::user();
        $productIds = $user->products()->pluck('id');

        $reviews = Review::query()
            ->with('reviewer', 'replies')
            ->where(function ($query) use ($productIds, $user) {
                $query->where(fn ($q) => $q->where('reviewable_type', ReviewableType::Product)->whereIn('reviewable_id', $productIds))
                    ->orWhere(fn ($q) => $q->where('reviewable_type', ReviewableType::Seller)->where('reviewable_id', $user->id));
            })
            ->latest()
            ->paginate(20);

        return view('seller.reviews.index', [
            'reviews' => $reviews,
            'routePrefix' => $user->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function reply(StoreReviewReplyRequest $request, Review $review): RedirectResponse
    {
        $user = Auth::user();
        $productIds = $user->products()->pluck('id')->all();

        $ownsReview = ($review->reviewable_type === ReviewableType::Product && in_array($review->reviewable_id, $productIds, true))
            || ($review->reviewable_type === ReviewableType::Seller && $review->reviewable_id === $user->id);

        abort_unless($ownsReview, 403);

        $this->reviews->reply($review, $user, $request->string('body')->value());

        return back()->with('status', 'Reply posted.');
    }
}
