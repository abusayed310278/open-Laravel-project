<?php

namespace App\Http\Requests\Onboarding;

use App\Enums\UserRole;
use App\Services\KycService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompleteProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, [UserRole::Business, UserRole::Saler], strict: true);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isBusiness = $user->role === UserRole::Business;

        $rules = [
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'description' => ['required', 'string', 'max:1000'],
            'country' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'phone' => $isBusiness ? ['nullable', 'string', 'max:30'] : ['prohibited'],
            'website' => $isBusiness ? ['nullable', 'url', 'max:255'] : ['prohibited'],
        ];

        /** @var KycService $kycService */
        $kycService = app(KycService::class);
        $requirements = $kycService->requirementsFor($user);

        foreach ($requirements as $req) {
            $key = "kyc_documents.{$req->document_type->value}";
            $docRules = ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'];

            if ($req->is_required) {
                array_unshift($docRules, 'required');
            } else {
                array_unshift($docRules, 'nullable');
            }

            $rules[$key] = $docRules;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'kyc_documents.*.required' => 'This KYC document is required by admin verification policy.',
            'kyc_documents.*.file' => 'Uploaded KYC document must be a valid file.',
            'kyc_documents.*.mimes' => 'Allowed document formats: PDF, JPG, JPEG, PNG, WEBP.',
            'kyc_documents.*.max' => 'Document size must not exceed 10MB.',
        ];
    }
}
