<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_date' => $this->payment_date->toDateString(),
            'amount' => $this->amount,
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'inn' => $this->client->inn,
            ],
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ],
            'work_direction' => $this->work_direction?->value,
            'work_direction_label' => $this->work_direction?->label(),
            'service_stage' => $this->service_stage,
            'invoice_number' => $this->invoice_number,
            'contract_number' => $this->contract_number,
            'payment_purpose' => $this->payment_purpose,
            'act' => new ActResource($this->whenLoaded('act')),
        ];
    }
}
