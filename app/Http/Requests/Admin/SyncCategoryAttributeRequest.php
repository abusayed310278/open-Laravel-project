<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncCategoryAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'attribute_ids'   => ['present', 'array'],
            'attribute_ids.*' => ['integer', 'exists:attributes,id'],
            'redirect_to'     => ['nullable', 'string'],
        ];
    }
}
