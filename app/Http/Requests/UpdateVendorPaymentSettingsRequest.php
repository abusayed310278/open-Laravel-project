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
            'stripe_enabled' => ['boolean'],
            'stripe_publishable_key' => ['nullable', 'string', 'max:500'],
            'stripe_secret_key' => ['nullable', 'string', 'max:500'],
            'paypal_enabled' => ['boolean'],
            'paypal_client_id' => ['nullable', 'string', 'max:500'],
            'paypal_client_secret' => ['nullable', 'string', 'max:500'],
            'cod_enabled' => ['boolean'],
            'manual_bank_enabled' => ['boolean'],
            'bank_details.bank_name' => ['nullable', 'string', 'max:255'],
            'bank_details.account_name' => ['nullable', 'string', 'max:255'],
            'bank_details.account_number' => ['nullable', 'string', 'max:100'],
            'bank_details.routing_number' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'in:connect_stripe,disconnect_stripe,connect_paypal,disconnect_paypal,save_all'],
        ];
    }
}
