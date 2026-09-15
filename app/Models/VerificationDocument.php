<?php

namespace App\Models;

use App\Enums\KycDocumentStatus;
use App\Enums\KycDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDocument extends Model
{
    protected $fillable = [
        'verification_id',
        'document_type',
        'document_number',
        'file_path',
        'expiry_date',
        'status',
        'verified_by',
        'verified_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'document_type' => KycDocumentType::class,
            'status' => KycDocumentStatus::class,
            'expiry_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(UserVerification::class, 'verification_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
