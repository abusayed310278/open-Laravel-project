<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'review_id',
        'path',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $image) => $image->created_at ??= now());
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
