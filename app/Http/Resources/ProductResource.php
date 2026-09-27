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
        if ($this->user) {
            if ($this->user->isBusiness() && $this->user->businessProfile?->logo) {
                $sellerLogo = MediaUrl::resolve($this->user->businessProfile->logo);
            } elseif ($this->user->isSaler() && $this->user->salerProfile?->logo) {
                $sellerLogo = MediaUrl::resolve($this->user->salerProfile->logo);
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
            'seller' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'role' => $this->user->role->value,
                'role_label' => $this->user->role->label(),
                'logo_url' => $sellerLogo,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
