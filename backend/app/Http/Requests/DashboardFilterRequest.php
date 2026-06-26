<?php

namespace App\Http\Requests;

use App\Domain\Enums\ActStatus;
use App\Domain\Enums\ProjectDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Валидация и сбор фильтров дашборда (общие для итогов и списка оплат).
 */
class DashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'client_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
            'direction' => ['nullable', Rule::enum(ProjectDirection::class)],
            'act_status' => ['nullable', Rule::enum(ActStatus::class)],
            'q' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, mixed> только присутствующие фильтры */
    public function filters(): array
    {
        return array_filter(
            $this->only(['from', 'to', 'client_id', 'project_id', 'direction', 'act_status', 'q']),
            fn ($value) => $value !== null && $value !== '',
        );
    }
}
