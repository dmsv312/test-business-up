<?php

namespace App\Models;

use App\Domain\Enums\ActStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Act extends Model
{
    protected $fillable = [
        'payment_id', 'is_sent', 'sent_at', 'is_signed', 'signed_at',
        'status', 'manager_comment',
    ];

    protected function casts(): array
    {
        return [
            'is_sent' => 'boolean',
            'is_signed' => 'boolean',
            'sent_at' => 'datetime',
            'signed_at' => 'datetime',
            'status' => ActStatus::class,
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
