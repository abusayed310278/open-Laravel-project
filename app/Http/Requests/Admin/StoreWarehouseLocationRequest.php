<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseLocationRequest extends FormRequest
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
            'zone' => ['required', 'string', 'max:20'],
            'row' => ['required', 'string', 'max:20'],
            'shelf' => ['required', 'string', 'max:20'],
            'slot' => ['required', 'string', 'max:20'],
        ];
    }
}
