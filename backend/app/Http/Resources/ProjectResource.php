<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\AggregatesPayments;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    use AggregatesPayments;

    public function toArray(Request $request): array
    {
        $payments = $this->payments;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ],
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'directions' => $payments
                ->map(fn (Payment $p) => $p->work_direction?->value)
                ->filter()->unique()->values(),
            'payments_count' => $payments->count(),
            'total_amount' => $this->totalAmount($payments),
            'acts' => $this->actsBreakdown($payments),
        ];
    }
}
