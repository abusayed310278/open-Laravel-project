<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationAppointment extends Model
{
    protected $fillable = [
        'product_verification_id',
        'appointment_date',
        'appointment_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'status' => AppointmentStatus::class,
        ];
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(ProductVerification::class, 'product_verification_id');
    }
}
