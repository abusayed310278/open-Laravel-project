<?php

namespace App\Http\Requests\Admin;

use App\Enums\AttributeType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAttributeRequest extends FormRequest
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
            'attribute_group_id'     => ['nullable', 'integer', 'exists:attribute_groups,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'slug'                   => ['nullable', 'string', 'max:255'],
            'type'                   => ['required', new Enum(AttributeType::class)],
            'unit'                   => ['nullable', 'string'],
            'placeholder'            => ['nullable', 'string', 'max:255'],
            'is_filterable'          => ['nullable', 'boolean'],
            'is_variant'             => ['nullable', 'boolean'],
            'is_required'            => ['nullable', 'boolean'],
            'is_active'              => ['nullable', 'boolean'],
            'sort_order'             => ['nullable', 'integer', 'min:0'],
            'values'                 => ['nullable', 'array'],
            'values.*'               => ['nullable', 'string'],
            'assign_to_category_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'redirect_to'            => ['nullable', 'string'],
        ];
    }
}
