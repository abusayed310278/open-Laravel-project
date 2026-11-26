<?php

namespace App\Models;

use App\Enums\ProductReportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'reporter_id',
        'reason',
        'details',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductReportStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
