<?php

namespace App\Models;

use App\Domain\Enums\OperationCategory;
use App\Domain\Enums\OperationDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BankOperation extends Model
{
    protected $fillable = [
        'op_date', 'direction', 'amount', 'doc_number',
        'counterparty_name', 'counterparty_inn', 'counterparty_account',
        'purpose', 'category', 'is_revenue',
    ];

    protected function casts(): array
    {
        return [
            'op_date' => 'date',
            'amount' => 'decimal:2',
            'direction' => OperationDirection::class,
            'category' => OperationCategory::class,
            'is_revenue' => 'boolean',
        ];
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
