<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class SellerUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $storeName = $this->name;
        $storeSlug = '';
        $logoUrl = null;
        $coverUrl = null;
        $description = null;
        $address = null;
        $city = null;
        $country = null;
        $isStoreActive = true;

        if ($this->isBusiness() && $this->businessProfile) {
            $profile = $this->businessProfile;
            $storeName = $profile->business_name ?: $this->name;
            $storeSlug = $profile->slug ?: '';
            $userAvatar = $this->profile?->avatarUrl() ?? ($this->avatar ? MediaUrl::resolve($this->avatar) : null);
            $logoUrl = $profile->logoUrl() ?? $userAvatar;
            $coverUrl = $profile->coverImageUrl();
            $description = $profile->description;
            $address = $profile->address;
            $city = $profile->city;
            $country = $profile->country;
            $isStoreActive = (bool) $profile->is_store_active;
        } elseif ($this->isSaler() && $this->salerProfile) {
            $profile = $this->salerProfile;
            $storeName = $profile->display_name ?: $this->name;
            $storeSlug = $profile->slug ?: '';
            $userAvatar = $this->profile?->avatarUrl() ?? ($this->avatar ? MediaUrl::resolve($this->avatar) : null);
            $logoUrl = $profile->logoUrl() ?? $userAvatar;
            $coverUrl = $profile->coverImageUrl();
            $description = $profile->bio;
            $address = $profile->location;
            $city = $profile->city;
            $country = $profile->country;
            $isStoreActive = (bool) $profile->is_store_active;
        }

        $latitude = isset($profile) && $profile->latitude ? (float) $profile->latitude : null;
        $longitude = isset($profile) && $profile->longitude ? (float) $profile->longitude : null;
        $website = isset($profile) ? ($profile->website ?? null) : null;
        $timezone = isset($profile) ? ($profile->timezone ?? null) : null;
        $businessHours = isset($profile) ? ($profile->business_hours ?? null) : null;
        $socialLinks = isset($profile) ? ($profile->social_links ?? null) : null;

        $subscription = $this->activeSubscription;
        $wallet = $this->wallet;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role?->value,
            'role_label' => $this->role?->label(),
            'store_name' => $storeName,
            'store_slug' => $storeSlug,
            'logo_url' => $logoUrl,
            'cover_url' => $coverUrl,
            'description' => $description,
            'company' => $this->profile?->company ?? ($this->businessProfile?->business_name ?? null),
            'location' => $this->profile?->location ?? ($address ?? null),
            'avatar_url' => $logoUrl ?? ($this->profile?->avatarUrl() ?? ($this->avatar ? MediaUrl::resolve($this->avatar) : null)),
            'address' => $address,
            'city' => $city,
            'country' => $country,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'website' => $website,
            'timezone' => $timezone,
            'business_hours' => $businessHours,
            'social_links' => $socialLinks,
            'is_store_active' => $isStoreActive,
            'is_kyc_approved' => $this->isKycApproved(),
            'listing_credits' => (int) ($subscription?->credits ?? 0),
            'subscription' => $subscription ? [
                'plan_name' => $subscription->plan?->name ?? 'Active Plan',
                'status' => $subscription->status?->value ?? 'active',
                'credits' => $subscription->credits,
                'expires_at' => $subscription->ends_at?->toIso8601String(),
            ] : null,
            'wallet' => [
                'available_balance' => (float) ($wallet?->available_balance ?? 0.0),
                'pending_balance' => (float) ($wallet?->pending_balance ?? 0.0),
                'total_earned' => (float) ($wallet?->total_earned ?? 0.0),
                'currency' => 'QAR',
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
