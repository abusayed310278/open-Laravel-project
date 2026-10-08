<?php

namespace App\Http\Controllers;

use App\Enums\ReviewableType;
use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\Review;
use App\Models\SalerProfile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorePageController extends Controller
{
    public function index(): View
    {
        return view('pages.stores', [
            'businesses' => BusinessProfile::query()->where('is_store_active', true)->orderBy('business_name')->get(),
            'salers' => SalerProfile::query()->where('is_store_active', true)->orderBy('display_name')->get(),
        ]);
    }

    public function business(Request $request, string $slug): View
    {
        $profile = BusinessProfile::query()
            ->where('slug', $slug)
            ->where('is_store_active', true)
            ->firstOrFail();

        return view('pages.store', [
            'profile' => $profile,
            'storeName' => $profile->business_name,
            'logo' => $profile->logo,
            'coverImage' => $profile->cover_image,
            'bio' => $profile->description,
            'location' => trim(implode(', ', array_filter([$profile->city, $profile->country]))),
            'isVerified' => $profile->user->isKycApproved() || $profile->user->hasActiveSubscription(),
            'products' => $this->products($request, $profile->user_id),
            'feedback' => $this->feedbackSummary($profile->user_id),
        ]);
    }

    public function saler(Request $request, string $slug): View
    {
        $profile = SalerProfile::query()
            ->where('slug', $slug)
            ->where('is_store_active', true)
            ->firstOrFail();

        return view('pages.store', [
            'profile' => $profile,
            'storeName' => $profile->display_name,
            'logo' => $profile->profile_photo,
            'coverImage' => $profile->cover_image,
            'bio' => $profile->bio,
            'location' => $profile->location ?: trim(implode(', ', array_filter([$profile->city, $profile->country]))),
            'isVerified' => $profile->user->isKycApproved() || $profile->user->hasActiveSubscription(),
            'products' => $this->products($request, $profile->user_id),
            'feedback' => $this->feedbackSummary($profile->user_id),
        ]);
    }

    /**
     * @return array{
     *     reviews: \Illuminate\Database\Eloquent\Collection<int, Review>,
     *     totalCount: int,
     *     positiveCount: int,
     *     neutralCount: int,
     *     negativeCount: int,
     *     positivePercent: int,
     *     averageRating: float,
     *     starCounts: array<int, int>,
     *     dsr: array{item_described: string, communication: string, shipping_speed: string, shipping_cost: string}
     * }
     */
    private function feedbackSummary(int $sellerUserId): array
    {
        $sellerUser = User::find($sellerUserId);
        $productIds = $sellerUser ? $sellerUser->products()->pluck('id')->all() : [];

        $reviews = Review::query()
            ->approved()
            ->with(['reviewer', 'replies', 'order.items'])
            ->where(function ($query) use ($productIds, $sellerUserId) {
                $query->where(fn ($q) => $q->where('reviewable_type', ReviewableType::Seller)->where('reviewable_id', $sellerUserId))
                    ->when(! empty($productIds), fn ($q) => $q->orWhere(fn ($sub) => $sub->where('reviewable_type', ReviewableType::Product)->whereIn('reviewable_id', $productIds)));
            })
            ->latest()
            ->get();

        $totalCount = $reviews->count();
        $positiveCount = $reviews->filter(fn (Review $r) => $r->rating >= 4)->count();
        $neutralCount  = $reviews->filter(fn (Review $r) => $r->rating === 3)->count();
        $negativeCount = $reviews->filter(fn (Review $r) => $r->rating <= 2)->count();

        $positivePercent = $totalCount > 0 ? (int) round(($positiveCount / $totalCount) * 100) : 100;
        $averageRating = $totalCount > 0 ? round((float) $reviews->avg('rating'), 1) : 5.0;

        $starCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        $dsr = [
            'item_described' => $totalCount > 0 ? number_format(min(5.0, $averageRating), 1) : '5.0',
            'communication'  => $totalCount > 0 ? number_format(min(5.0, max(4.0, $averageRating)), 1) : '5.0',
            'shipping_speed' => $totalCount > 0 ? number_format(min(5.0, $averageRating), 1) : '5.0',
            'shipping_cost'  => 'Free / Fair',
        ];

        return [
            'reviews' => $reviews,
            'totalCount' => $totalCount,
            'positiveCount' => $positiveCount,
            'neutralCount' => $neutralCount,
            'negativeCount' => $negativeCount,
            'positivePercent' => $positivePercent,
            'averageRating' => $averageRating,
            'starCounts' => $starCounts,
            'dsr' => $dsr,
        ];
    }

    private function products(Request $request, int $userId): LengthAwarePaginator
    {
        return Product::query()
            ->live()
            ->where('user_id', $userId)
            ->with('images')
            ->when($request->filled('condition'), fn ($q) => $q->where('condition', $request->string('condition')))
            ->tap(fn ($query) => match ($request->string('sort')->value()) {
                'price_asc' => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                default => $query->orderByDesc('published_at'),
            })
            ->paginate(12)
            ->withQueryString();
    }
}
