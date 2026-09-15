<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PhoneVerificationService
{
    private const TTL_MINUTES = 10;

    /**
     * Generate and "send" a one-time code for the user's phone number.
     *
     * No SMS gateway is wired up yet, so in every environment the code is
     * written to the log instead — swap this for a real gateway call
     * (Twilio, Vonage, etc.) once one is chosen.
     */
    public function send(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($user), $code, now()->addMinutes(self::TTL_MINUTES));

        Log::info("Phone verification code for {$user->phone}: {$code}");
    }

    public function verify(User $user, string $code): bool
    {
        $expected = Cache::get($this->cacheKey($user));

        if ($expected === null || ! hash_equals($expected, $code)) {
            return false;
        }

        Cache::forget($this->cacheKey($user));
        $user->forceFill(['phone_verified_at' => now()])->save();

        return true;
    }

    private function cacheKey(User $user): string
    {
        return "phone-otp:{$user->id}";
    }
}
