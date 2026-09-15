<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\Product;
use App\Models\SalerProfile;
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
            'isVerified' => $profile->user->isKycApproved(),
            'products' => $this->products($request, $profile->user_id),
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
            'isVerified' => $profile->user->isKycApproved(),
            'products' => $this->products($request, $profile->user_id),
        ]);
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
