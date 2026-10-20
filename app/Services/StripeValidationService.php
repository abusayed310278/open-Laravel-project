<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeValidationService
{
    /**
     * Validate publishable key format.
     */
    public function validatePublishableKeyFormat(?string $key): ?string
    {
        if (blank($key)) {
            return null;
        }

        $key = trim($key);

        if (! preg_match('/^pk_(test|live)_[a-zA-Z0-9_]{20,}$/', $key)) {
            if (str_starts_with($key, 'sk_') || str_starts_with($key, 'rk_')) {
                return 'You entered a Secret Key into the Publishable Key field. Publishable keys must start with pk_live_ or pk_test_.';
            }

            if (str_starts_with($key, 'mk_')) {
                return 'Invalid key: Keys starting with "mk_" are Merchant IDs, not Stripe Publishable API keys. Publishable keys start with pk_live_ or pk_test_.';
            }

            return 'Invalid Stripe Publishable Key format. Must start with pk_live_ or pk_test_ followed by valid key characters.';
        }

        return null;
    }

    /**
     * Validate secret key format.
     */
    public function validateSecretKeyFormat(?string $key): ?string
    {
        if (blank($key)) {
            return null;
        }

        $key = trim($key);

        if (! preg_match('/^(sk|rk)_(test|live)_[a-zA-Z0-9_]{20,}$/', $key)) {
            if (str_starts_with($key, 'pk_')) {
                return 'You entered a Publishable Key into the Secret Key field. Secret keys must start with sk_live_ or sk_test_.';
            }

            if (str_starts_with($key, 'mk_')) {
                return 'Invalid key: Keys starting with "mk_" are Merchant/Master IDs, not Stripe Secret API keys. Your Stripe secret key must start with sk_live_ (or sk_test_).';
            }

            return 'Invalid Stripe Secret Key format. Secret keys must start with sk_live_ or sk_test_ (or rk_live_ / rk_test_ for restricted keys).';
        }

        return null;
    }

    /**
     * Validate that publishable and secret keys belong to the same mode (test vs live).
     */
    public function validateModeMatch(?string $publishableKey, ?string $secretKey): ?string
    {
        if (blank($publishableKey) || blank($secretKey)) {
            return null;
        }

        $isPubLive = str_starts_with(trim($publishableKey), 'pk_live_');
        $isSecLive = str_contains(trim($secretKey), '_live_');

        if ($isPubLive !== $isSecLive) {
            $pubMode = $isPubLive ? 'Live' : 'Test';
            $secMode = $isSecLive ? 'Live' : 'Test';

            return "Key mode mismatch: Publishable key is {$pubMode} mode, but Secret key is {$secMode} mode. Both keys must be in the same mode.";
        }

        return null;
    }

    /**
     * Perform live API verification with Stripe.
     *
     * @return array{valid: bool, error: ?string, livemode: ?bool}
     */
    public function verifySecretKeyWithStripe(string $secretKey): array
    {
        $secretKey = trim($secretKey);

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(8)
                ->get('https://api.stripe.com/v1/balance');

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'valid' => true,
                    'error' => null,
                    'livemode' => (bool) ($data['livemode'] ?? str_contains($secretKey, '_live_')),
                ];
            }

            $errorMsg = $response->json('error.message') ?? 'Stripe rejected this API key.';

            return [
                'valid' => false,
                'error' => 'Stripe verification failed: ' . $errorMsg,
                'livemode' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Stripe key verification network issue: ' . $e->getMessage());

            // If network timed out or connection error
            return [
                'valid' => false,
                'error' => 'Unable to reach Stripe API to verify key: ' . $e->getMessage(),
                'livemode' => null,
            ];
        }
    }
}
