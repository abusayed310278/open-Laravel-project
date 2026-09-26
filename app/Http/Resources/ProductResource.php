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
        if ($this->seller) {
            if ($this->seller->isBusiness() && $this->seller->businessProfile?->logo) {
                $sellerLogo = MediaUrl::resolve($this->seller->businessProfile->logo);
            } elseif ($this->seller->isSaler() && $this->seller->sellerProfile?->logo) {
                $sellerLogo = MediaUrl::resolve($this->seller->sellerProfile->logo);
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'compare_price' => $this->compare_price ? (float) $this->compare_price : null,
            'stock' => (int) $this->stock,
            'sku' => $this->sku,
            'image' => MediaUrl::resolve($this->featured_image ?? $this->image),
            'gallery' => collect($this->gallery ?? [])->map(fn ($img) => MediaUrl::resolve($img))->values()->toArray(),
            'rating' => (float) ($this->reviews_avg_rating ?? $this->rating ?? 0),
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
            'seller' => $this->whenLoaded('seller', fn () => [
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'role' => $this->seller->role->value,
                'role_label' => $this->seller->role->label(),
                'logo_url' => $sellerLogo,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
