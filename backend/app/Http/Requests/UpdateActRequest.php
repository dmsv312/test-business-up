<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_sent' => ['sometimes', 'boolean'],
            'is_signed' => ['sometimes', 'boolean'],
            'manager_comment' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
