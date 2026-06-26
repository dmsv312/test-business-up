<?php

namespace App\Http\Resources;

use App\Models\Act;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Act */
class ActResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_sent' => $this->is_sent,
            'sent_at' => $this->sent_at,
            'is_signed' => $this->is_signed,
            'signed_at' => $this->signed_at,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'manager_comment' => $this->manager_comment,
        ];
    }
}
