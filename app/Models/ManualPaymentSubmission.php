<?php

namespace App\Models;

use App\Enums\PaymentSubmissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ManualPaymentSubmission extends Model
{
    protected $fillable = [
        'order_id',
        'vendor_order_id',
        'amount',
        'reference',
        'bank_name',
        'proof_file_path',
        'status',
        'verified_by',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentSubmissionStatus::class,
            'amount' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function proofUrl(): string
    {
        return Storage::disk('local')->temporaryUrl($this->proof_file_path, now()->addMinutes(5));
    }
}
