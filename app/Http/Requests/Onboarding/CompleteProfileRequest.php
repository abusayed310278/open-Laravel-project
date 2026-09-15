<?php

namespace App\Http\Requests\Onboarding;

use App\Enums\UserRole;
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
        $isBusiness = $this->user()->role === UserRole::Business;

        return [
            'logo' => ['nullable', 'image', 'max:2048'],
            'description' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:120'],
            'phone' => $isBusiness ? ['nullable', 'string', 'max:30'] : ['prohibited'],
            'website' => $isBusiness ? ['nullable', 'url', 'max:255'] : ['prohibited'],
        ];
    }
}
