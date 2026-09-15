<?php

namespace App\Models;

use App\Enums\ProductGrade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGradeAssignment extends Model
{
    protected $fillable = [
        'product_id',
        'product_verification_id',
        'verifier_id',
        'grade',
        'grade_notes',
        'battery_health',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'grade' => ProductGrade::class,
            'assigned_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(ProductVerification::class, 'product_verification_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
