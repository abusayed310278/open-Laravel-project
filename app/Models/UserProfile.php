<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'date_of_birth',
        'gender',
        'company',
        'location',
        'designation',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->avatar);
    }
}
