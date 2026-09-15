<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiveWarehouseProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'warehouse_location_id' => ['required', 'integer', 'exists:warehouse_locations,id'],
            'condition_at_receipt' => ['nullable', Rule::in(['A', 'B', 'C'])],
            'condition_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
