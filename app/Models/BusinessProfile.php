<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'logo',
        'cover_image',
        'description',
        'trade_license_number',
        'vat_number',
        'tax_number',
        'address',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'phone',
        'website',
        'social_links',
        'business_hours',
        'timezone',
        'is_store_active',
        'profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'business_hours' => 'array',
            'is_store_active' => 'boolean',
            'profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logoUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->logo);
    }

    public function coverImageUrl(): ?string
    {
        $adminSellerBanner = setting('seller_banner');

        return (trim((string) $this->cover_image) !== '' ? \App\Support\MediaUrl::resolve($this->cover_image) : null)
            ?: (trim((string) $adminSellerBanner) !== '' ? \App\Support\MediaUrl::resolve($adminSellerBanner) : null)
            ?: asset('images/default-cover.svg');
    }
}
