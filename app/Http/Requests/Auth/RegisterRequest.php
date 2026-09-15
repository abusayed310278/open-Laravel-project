<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
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
        return [
            'account_type' => ['required', Rule::in(['customer', 'business', 'saler'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],

            'business_name' => ['required_if:account_type,business', 'nullable', 'string', 'max:255'],

            'display_name' => ['required_if:account_type,saler', 'nullable', 'string', 'max:255'],
            'location' => ['required_if:account_type,saler', 'nullable', 'string', 'max:255'],
        ];
    }

    public function role(): UserRole
    {
        return UserRole::from($this->string('account_type')->value());
    }
}
