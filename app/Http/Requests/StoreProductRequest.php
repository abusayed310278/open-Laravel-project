<?php

namespace App\Http\Requests;

use App\Enums\ProductCondition;
use App\Enums\ShippingType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'short_description' => ['nullable', 'string', 'max:5000'],
            'condition' => ['required', new Enum(ProductCondition::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'gt:price'],
            'sku' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'is_negotiable' => ['boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'shipping_type' => ['required', new Enum(ShippingType::class)],
            'shipping_flat_rate' => ['required_if:shipping_type,flat_rate', 'nullable', 'numeric', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['file', 'image', 'max:4096'],
            'attributes' => ['nullable', 'array'],
        ];

        if ($this->filled('category_id')) {
            $category = Category::query()->find($this->integer('category_id'));

            foreach ($category?->attributes ?? [] as $attribute) {
                if ($attribute->pivot->is_required) {
                    $rules["attributes.{$attribute->id}"] = ['required'];
                }
            }
        }

        return $rules;
    }
}
