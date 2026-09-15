<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

class SettingsService
{
    private const CACHE_KEY = 'settings:all';

    /**
     * Keys whose values are encrypted at rest (SMTP password, R2 secret
     * key, ...). Never render these back into an edit form.
     */
    private const ENCRYPTED_KEYS = [
        'mail_password',
        'r2_secret_access_key',
        'stripe_secret_key',
        'paypal_client_secret',
    ];

    /**
     * @return array<string, string|null>
     */
    public function all(): array
    {
        // Every page (including the 500/503 error views) reads settings for
        // branding, so a DB outage here must degrade to "no settings" rather
        // than taking the error page down with it.
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (Throwable $e) {
            Log::error('Settings unavailable, falling back to defaults: '.$e->getMessage());

            return [];
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Read and decrypt a value stored via an encrypted key. Returns null if
     * unset rather than throwing when nothing has been saved yet.
     */
    public function getDecrypted(string $key): ?string
    {
        $value = $this->get($key);

        return $value !== null ? Crypt::decryptString($value) : null;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * @param  array<string, string|null>  $values
     */
    public function setMany(array $values, string $group): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    public function set(string $key, ?string $value, string $group = 'general'): void
    {
        if ($value !== null && in_array($key, self::ENCRYPTED_KEYS, strict: true)) {
            $value = Crypt::encryptString($value);
        }

        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
