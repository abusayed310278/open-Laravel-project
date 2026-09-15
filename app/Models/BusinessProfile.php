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
        'city',
        'country',
        'phone',
        'website',
        'social_links',
        'is_store_active',
        'profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'is_store_active' => 'boolean',
            'profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
