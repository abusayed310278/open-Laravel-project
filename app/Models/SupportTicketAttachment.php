<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_message_id',
        'file_path',
        'file_name',
    ];

    protected static function booted(): void
    {
        static::creating(fn (self $attachment) => $attachment->created_at ??= now());
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(SupportTicketMessage::class, 'ticket_message_id');
    }
}
