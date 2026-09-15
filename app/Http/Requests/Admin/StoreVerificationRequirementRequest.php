<?php

namespace App\Http\Requests\Admin;

use App\Enums\KycDocumentType;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreVerificationRequirementRequest extends FormRequest
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
            'role' => ['required', Rule::in([UserRole::Business->value, UserRole::Saler->value])],
            'document_type' => ['required', new Enum(KycDocumentType::class)],
            'is_required' => ['boolean'],
        ];
    }
}
