<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sellerLogo = null;
        $bannerUrl = null;
        if ($this->user) {
            if ($this->user->isBusiness() && $this->user->businessProfile?->logo) {
                $sellerLogo = MediaUrl::resolve($this->user->businessProfile->logo);
            } elseif ($this->user->isSaler() && $this->user->salerProfile?->logo) {
                $sellerLogo = MediaUrl::resolve($this->user->salerProfile->logo);
            }

            // Fallback to user profile avatar (for Admin, Store Owner, or Seller without custom logo)
            if (! $sellerLogo && $this->user->profile?->avatar) {
                $sellerLogo = MediaUrl::resolve($this->user->profile->avatar);
            }

            // Fallback for Admin account without uploaded avatar
            if (! $sellerLogo && $this->user->isAdmin()) {
                $brandLogo = setting('brand_logo');
                $sellerLogo = $brandLogo ? MediaUrl::resolve($brandLogo) : MediaUrl::resolve('icon.png');
            }

            if ($this->user->isAdmin()) {
                $bannerSetting = setting('brand_banner') ?: setting('brand_cover_image');
                $bannerUrl = $bannerSetting ? MediaUrl::resolve($bannerSetting) : (file_exists(public_path('banner image.png')) ? asset('banner image.png') : null);
            } elseif ($this->user->isBusiness() && $this->user->businessProfile?->cover_image) {
                $bannerUrl = MediaUrl::resolve($this->user->businessProfile->cover_image);
            } elseif ($this->user->isSaler() && $this->user->salerProfile?->cover_image) {
                $bannerUrl = MediaUrl::resolve($this->user->salerProfile->cover_image);
            }
            if (! $bannerUrl && file_exists(public_path('banner image.png'))) {
                $bannerUrl = asset('banner image.png');
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'compare_price' => $this->compare_price ? (float) $this->compare_price : null,
            'stock' => (int) ($this->quantity ?? 0),
            'sku' => $this->sku,
            'image' => MediaUrl::resolve($this->primaryImageUrl()),
            'gallery' => collect($this->images ?? [])->map(fn ($img) => MediaUrl::resolve($img->image_path))->values()->toArray(),
            'rating' => (float) ($this->reviews_avg_rating ?? 0),
            'reviews_count' => (int) ($this->reviews_count ?? 0),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ]),
            'seller' => $this->whenLoaded('user', function () use ($sellerLogo, $bannerUrl) {
                $city = $this->user->profile?->location 
                    ?: ($this->user->businessProfile?->city ?: ($this->user->salerProfile?->city ?: 'Doha, Qatar'));
                $productCount = (int) ($this->user->products_count ?? $this->user->products()->count());

                return [
                    'id' => (string) $this->user->id,
                    'name' => $this->user->name,
                    'role' => $this->user->role->value,
                    'role_label' => $this->user->role->label(),
                    'logo_url' => $sellerLogo,
                    'avatar_url' => $sellerLogo,
                    'banner_url' => $bannerUrl,
                    'cover_url' => $bannerUrl,
                    'city' => $city,
                    'rating' => 4.8,
                    'review_count' => $productCount > 0 ? $productCount * 3 : 0,
                    'product_count' => $productCount,
                    'is_verified' => $this->user->isAdmin() || $this->user->isKycApproved(),
                    'member_since' => $this->user->created_at?->format('M Y') ?? '2024',
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
