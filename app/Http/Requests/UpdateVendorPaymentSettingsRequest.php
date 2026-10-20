<?php

namespace App\Http\Requests;

use App\Services\StripeValidationService;
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

    /**
     * Configure the validator instance with Stripe live verification.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $action = $this->input('action');
            $pubKey = $this->input('stripe_publishable_key');
            $secKey = $this->input('stripe_secret_key');
            $enabled = $this->boolean('stripe_enabled');

            if ($action === 'disconnect_stripe') {
                return;
            }

            $isConnectingOrConfiguring = $action === 'connect_stripe' || filled($pubKey) || filled($secKey) || $enabled;

            if (! $isConnectingOrConfiguring) {
                return;
            }

            $stripeService = app(StripeValidationService::class);

            if ($action === 'connect_stripe') {
                if (blank($pubKey)) {
                    $validator->errors()->add('stripe_publishable_key', 'Stripe Publishable Key is required to connect Stripe.');
                }
                if (blank($secKey)) {
                    $validator->errors()->add('stripe_secret_key', 'Stripe Secret Key is required to connect Stripe.');
                }
            }

            // 1. Format check on publishable key
            if (filled($pubKey)) {
                if ($err = $stripeService->validatePublishableKeyFormat($pubKey)) {
                    $validator->errors()->add('stripe_publishable_key', $err);
                }
            }

            // 2. Format check on secret key
            if (filled($secKey)) {
                if ($err = $stripeService->validateSecretKeyFormat($secKey)) {
                    $validator->errors()->add('stripe_secret_key', $err);
                }
            }

            // 3. Mode match check
            if (filled($pubKey) && filled($secKey)) {
                if ($err = $stripeService->validateModeMatch($pubKey, $secKey)) {
                    $validator->errors()->add('stripe_secret_key', $err);
                }
            }

            // 4. Live Stripe API verification
            if (filled($secKey) && ! $validator->errors()->has('stripe_secret_key')) {
                $verification = $stripeService->verifySecretKeyWithStripe($secKey);
                if (! $verification['valid']) {
                    $validator->errors()->add('stripe_secret_key', $verification['error']);
                }
            }
        });
    }
}

