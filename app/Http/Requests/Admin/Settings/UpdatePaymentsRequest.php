<?php

namespace App\Http\Requests\Admin\Settings;

use App\Services\SettingsService;
use App\Services\StripeValidationService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentsRequest extends FormRequest
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
            'stripe_enabled' => ['boolean'],
            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'paypal_enabled' => ['boolean'],
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_client_secret' => ['nullable', 'string', 'max:255'],
            'bank_details' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance with Stripe live verification.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $stripeService = app(StripeValidationService::class);
            $pubKey = $this->input('stripe_publishable_key');
            $secKey = $this->input('stripe_secret_key');
            $enabled = $this->boolean('stripe_enabled');

            // 1. Format check on publishable key
            if (filled($pubKey)) {
                if ($err = $stripeService->validatePublishableKeyFormat($pubKey)) {
                    $validator->errors()->add('stripe_publishable_key', $err);
                }
            }

            // 2. Format check on secret key if provided
            if (filled($secKey)) {
                if ($err = $stripeService->validateSecretKeyFormat($secKey)) {
                    $validator->errors()->add('stripe_secret_key', $err);
                }
            }

            // 3. Mode match check if both keys are provided
            if (filled($pubKey) && filled($secKey)) {
                if ($err = $stripeService->validateModeMatch($pubKey, $secKey)) {
                    $validator->errors()->add('stripe_secret_key', $err);
                }
            }

            // 4. Live Stripe API verification if a new secret key was entered
            if (filled($secKey) && ! $validator->errors()->has('stripe_secret_key')) {
                $verification = $stripeService->verifySecretKeyWithStripe($secKey);
                if (! $verification['valid']) {
                    $validator->errors()->add('stripe_secret_key', $verification['error']);
                }
            }

            // 5. If Stripe is being enabled without entering a new secret key, verify existing stored key
            if ($enabled && blank($secKey) && ! $validator->errors()->has('stripe_enabled')) {
                $existingSecret = app(SettingsService::class)->getDecrypted('stripe_secret_key') ?: config('services.stripe.secret');
                if (blank($existingSecret)) {
                    $validator->errors()->add('stripe_secret_key', 'Cannot enable Stripe without configuring a valid Secret Key.');
                } else {
                    $verification = $stripeService->verifySecretKeyWithStripe($existingSecret);
                    if (! $verification['valid']) {
                        $validator->errors()->add('stripe_secret_key', 'Current stored Stripe key is invalid: ' . $verification['error']);
                    }
                }
            }
        });
    }
}

