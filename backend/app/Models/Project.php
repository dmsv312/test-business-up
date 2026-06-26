<?php

namespace App\Models;

use App\Domain\Enums\ProjectDirection;
use App\Domain\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'client_id', 'name', 'direction', 'contract_number', 'status',
    ];

    protected function casts(): array
    {
        return [
            'direction' => ProjectDirection::class,
            'status' => ProjectStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
