<?php

namespace App\Models;

use App\Enums\ChecklistResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationResult extends Model
{
    protected $fillable = [
        'product_verification_id',
        'checklist_item_id',
        'result',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'result' => ChecklistResult::class,
        ];
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(ProductVerification::class, 'product_verification_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(VerificationChecklist::class, 'checklist_item_id');
    }
}
