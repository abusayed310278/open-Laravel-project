<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReply extends Model
{
    protected $fillable = [
        'review_id',
        'replier_id',
        'body',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function replier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replier_id');
    }
}
