<?php

namespace App\Http\Resources;

use App\Models\BankOperation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin BankOperation */
class BankOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'op_date' => $this->op_date->toDateString(),
            'direction' => $this->direction->value,
            'direction_label' => $this->direction->label(),
            'amount' => $this->amount,
            'counterparty_name' => $this->counterparty_name,
            'counterparty_inn' => $this->counterparty_inn,
            'purpose' => $this->purpose,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'is_revenue' => $this->is_revenue,
        ];
    }
}
