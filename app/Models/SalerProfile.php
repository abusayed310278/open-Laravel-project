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
        'city',
        'country',
        'is_store_active',
        'profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'is_store_active' => 'boolean',
            'profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
