<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkCategoryAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'attribute_ids'   => ['required', 'array', 'min:1'],
            'attribute_ids.*' => ['integer', 'exists:attributes,id'],
            'redirect_to'     => ['nullable', 'string'],
        ];
    }
}
