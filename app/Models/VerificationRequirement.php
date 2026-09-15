<?php

namespace App\Models;

use App\Enums\KycDocumentType;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

class VerificationRequirement extends Model
{
    protected $fillable = [
        'role',
        'document_type',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'document_type' => KycDocumentType::class,
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
