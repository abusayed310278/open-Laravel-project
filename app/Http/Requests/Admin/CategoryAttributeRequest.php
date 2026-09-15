<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'attribute_id'  => ['sometimes', 'required', 'exists:attributes,id'],
            'is_required'   => ['nullable', 'boolean'],
            'is_filterable' => ['nullable', 'boolean'],
            'is_variant'    => ['nullable', 'boolean'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
        ];
    }
}
