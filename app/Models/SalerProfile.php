<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'slug',
        'profile_photo',
        'cover_image',
        'bio',
        'location',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
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
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLogoAttribute(): ?string
    {
        return $this->profile_photo;
    }

    public function logoUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->profile_photo);
    }

    public function coverImageUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->cover_image);
    }
}
