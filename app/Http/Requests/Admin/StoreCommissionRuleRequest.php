<?php

namespace App\Http\Requests\Admin;

use App\Enums\CommissionRuleType;
use App\Enums\CommissionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommissionRuleRequest extends FormRequest
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
            'type' => ['required', Rule::enum(CommissionRuleType::class)],
            'reference_id' => ['nullable', 'integer'],
            'commission_type' => ['required', Rule::enum(CommissionType::class)],
            'value' => ['required', 'numeric', 'min:0'],
            'priority' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ];
    }
}
