<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorPaymentSettingsRequest extends FormRequest
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
            'cod_enabled' => ['boolean'],
            'manual_bank_enabled' => ['boolean'],
            'bank_details.bank_name' => ['nullable', 'string', 'max:255'],
            'bank_details.account_name' => ['nullable', 'string', 'max:255'],
            'bank_details.account_number' => ['nullable', 'string', 'max:100'],
            'bank_details.routing_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}
