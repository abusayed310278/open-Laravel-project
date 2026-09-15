<?php

namespace App\Http\Controllers;

use App\Enums\ReviewableType;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Order;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function index(): View
    {
        return view('account.reviews.index', [
            'reviews' => Auth::user()->reviewsWritten()->with('order')->latest()->paginate(15),
        ]);
    }

    public function store(StoreReviewRequest $request, Order $order): RedirectResponse
    {
        $this->reviews->create(
            Auth::user(),
            $order,
            ReviewableType::from($request->string('reviewable_type')->value()),
            $request->integer('reviewable_id'),
            $request->integer('rating'),
            $request->string('title')->value() ?: null,
            $request->string('body')->value(),
        );

        return back()->with('status', 'Thanks for your review — it will show once approved.');
    }
}
