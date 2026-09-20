<?php

use App\Services\SiteManagementService;
use Illuminate\Support\Facades\Auth;

if (!function_exists('feature_enabled')) {
    /**
     * Check if a feature toggle (homepage section or product page component) is enabled.
     */
    function feature_enabled(string $key): bool
    {
        return app(SiteManagementService::class)->isFeatureEnabled($key);
    }
}

if (!function_exists('user_can')) {
    /**
     * Check if the currently authenticated user has the given permission slug.
     */
    function user_can(?string $permissionSlug): bool
    {
        if (empty($permissionSlug)) {
            return true;
        }

        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->hasPermission($permissionSlug);
    }
}
