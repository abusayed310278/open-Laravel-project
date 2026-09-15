<?php

namespace App\Models;

use App\Enums\CommissionRuleType;
use App\Enums\CommissionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = [
        'type',
        'reference_id',
        'commission_type',
        'value',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommissionRuleType::class,
            'commission_type' => CommissionType::class,
            'value' => 'decimal:4',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
