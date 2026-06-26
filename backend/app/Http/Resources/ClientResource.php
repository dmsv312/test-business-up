<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\AggregatesPayments;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Client */
class ClientResource extends JsonResource
{
    use AggregatesPayments;

    public function toArray(Request $request): array
    {
        $payments = $this->payments;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'inn' => $this->inn,
            'ogrn' => $this->ogrn,
            'payments_count' => $payments->count(),
            'total_amount' => $this->totalAmount($payments),
            'acts' => $this->actsBreakdown($payments),
        ];
    }
}
