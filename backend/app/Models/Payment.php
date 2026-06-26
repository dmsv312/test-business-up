<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'client_id', 'project_id', 'bank_operation_id', 'payment_date', 'amount',
        'payment_purpose', 'service_stage', 'invoice_number', 'contract_number',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function bankOperation(): BelongsTo
    {
        return $this->belongsTo(BankOperation::class);
    }

    public function act(): HasOne
    {
        return $this->hasOne(Act::class);
    }
}
