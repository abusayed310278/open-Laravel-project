<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewableType;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            ->simplePaginate(15)
            ->withQueryString();

        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->limit(150)->get();
        $products = Product::query()->select('id', 'title')->orderBy('title')->limit(100)->get();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statuses' => ReviewStatus::cases(),
            'reviewableTypes' => ReviewableType::cases(),
            'users' => $users,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'city' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ReviewStatus::class)],
            'reviewable_type' => ['nullable', Rule::enum(ReviewableType::class)],
            'reviewable_id' => ['nullable', 'integer'],
        ]);

        if (empty($validated['reviewer_name']) && empty($validated['reviewer_id'])) {
            $validated['reviewer_name'] = $request->user()?->name ?? 'Customer';
        }

        // If target product or seller was chosen, ensure both type and ID are paired
        if (!empty($validated['reviewable_type']) && empty($validated['reviewable_id'])) {
            $validated['reviewable_type'] = null;
        }

        Review::create($validated);

        return back()->with('status', 'Review created successfully.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewer_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(ReviewStatus::class)],
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
