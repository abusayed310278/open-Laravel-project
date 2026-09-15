<?php

use App\Services\SettingsService;

if (! function_exists('setting')) {
    /**
     * Read a value from the admin Settings module (branding, mail, storage,
     * system). Falls back to $default when unset.
     */
    function setting(string $key, ?string $default = null): ?string
    {
        return app(SettingsService::class)->get($key, $default);
    }
}
