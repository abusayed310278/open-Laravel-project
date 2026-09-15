<?php

namespace App\Http\Requests;

use App\Services\KycService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SubmitKycRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'documents' => ['array'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_numbers' => ['array'],
            'document_numbers.*' => ['nullable', 'string', 'max:100'],
        ];

        /** @var KycService $kyc */
        $kyc = app(KycService::class);

        foreach ($kyc->requirementsFor($this->user()) as $requirement) {
            if ($requirement->is_required) {
                $rules["documents.{$requirement->document_type->value}"] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
            }
        }

        return $rules;
    }
}
