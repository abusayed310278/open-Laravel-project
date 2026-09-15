<?php

namespace App\Models;

use App\Enums\ReviewReportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReport extends Model
{
    protected $fillable = [
        'review_id',
        'reporter_id',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReviewReportStatus::class,
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
