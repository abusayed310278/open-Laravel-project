<?php

namespace App\Services;

use App\Models\DashboardMenu;
use App\Models\FeatureSetting;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SiteManagementService
{
    private const CACHE_TTL = 86400; // 24 hours (cleared on admin update)

    /**
     * Check if a feature toggle (homepage section or product page component) is enabled.
     */
    public function isFeatureEnabled(string $key): bool
    {
        $features = Cache::remember('site_feature_settings', self::CACHE_TTL, function () {
            return FeatureSetting::pluck('is_enabled', 'key')->toArray();
        });

        return $features[$key] ?? true;
    }

    /**
     * Get all feature settings for a specific group (e.g. 'homepage' or 'product_page').
     */
    public function getFeatureGroup(string $group): Collection
    {
        return $this->rememberCollection("site_feature_group_{$group}", function () use ($group) {
            return FeatureSetting::group($group)->ordered()->get();
        });
    }

    /**
     * Get dynamic dashboard menus for a specific role slug.
     */
    public function getMenuForRole(string $roleSlug): Collection
    {
        return $this->rememberCollection("dashboard_menu_{$roleSlug}", function () use ($roleSlug) {
            return DashboardMenu::forRole($roleSlug)->enabled()->ordered()->get();
        });
    }

    /**
     * Safely remember a collection in cache with guard against __PHP_Incomplete_Class.
     */
    private function rememberCollection(string $key, \Closure $callback): Collection
    {
        $value = Cache::remember($key, self::CACHE_TTL, $callback);

        if (! $value instanceof Collection || is_a($value, '__PHP_Incomplete_Class')) {
            Cache::forget($key);
            $value = $callback();
            Cache::put($key, $value, self::CACHE_TTL);
        }

        return $value;
    }

    /**
     * Enable or disable a feature toggle key.
     */
    public function toggleFeature(string $key, ?bool $isEnabled = null): bool
    {
        $feature = FeatureSetting::where('key', $key)->firstOrFail();
        $newState = $isEnabled ?? !$feature->is_enabled;
        $feature->update(['is_enabled' => $newState]);

        $this->clearCache();

        return $newState;
    }

    /**
     * Save/update a feature setting detail.
     */
    public function updateFeature(int $id, array $data): FeatureSetting
    {
        $feature = FeatureSetting::findOrFail($id);
        $feature->update([
            'title' => $data['title'] ?? $feature->title,
            'description' => $data['description'] ?? $feature->description,
            'is_enabled' => isset($data['is_enabled']) ? (bool) $data['is_enabled'] : $feature->is_enabled,
            'sort_order' => $data['sort_order'] ?? $feature->sort_order,
        ]);

        $this->clearCache();

        return $feature;
    }

    /**
     * Update permissions assigned to a role.
     */
    public function updateRolePermissions(int $roleId, array $permissionIds): Role
    {
        $role = Role::findOrFail($roleId);
        $role->permissions()->sync($permissionIds);

        $this->clearCache();

        return $role;
    }

    /**
     * Update a dashboard menu item.
     */
    public function updateDashboardMenu(int $menuId, array $data): DashboardMenu
    {
        $menu = DashboardMenu::findOrFail($menuId);
        $menu->update([
            'title' => $data['title'] ?? $menu->title,
            'route_name' => $data['route_name'] ?? $menu->route_name,
            'group_name' => $data['group_name'] ?? $menu->group_name,
            'icon' => $data['icon'] ?? $menu->icon,
            'permission_slug' => $data['permission_slug'] ?? $menu->permission_slug,
            'is_enabled' => isset($data['is_enabled']) ? (bool) $data['is_enabled'] : $menu->is_enabled,
            'sort_order' => $data['sort_order'] ?? $menu->sort_order,
        ]);

        $this->clearCache();

        return $menu;
    }

    /**
     * Create a new dashboard menu item for a role.
     */
    public function createDashboardMenu(array $data): DashboardMenu
    {
        $menu = DashboardMenu::create([
            'role_slug' => $data['role_slug'],
            'title' => $data['title'],
            'route_name' => $data['route_name'] ?? null,
            'url_path' => $data['url_path'] ?? null,
            'icon' => $data['icon'] ?? null,
            'group_name' => $data['group_name'] ?? 'NAVIGATION',
            'permission_slug' => $data['permission_slug'] ?? null,
            'is_enabled' => isset($data['is_enabled']) ? (bool) $data['is_enabled'] : true,
            'sort_order' => $data['sort_order'] ?? (DashboardMenu::where('role_slug', $data['role_slug'])->max('sort_order') + 1),
        ]);

        $this->clearCache();

        return $menu;
    }

    /**
     * Delete a dashboard menu item.
     */
    public function deleteDashboardMenu(int $menuId): void
    {
        DashboardMenu::destroy($menuId);
        $this->clearCache();
    }

    /**
     * Clear all cached site management data.
     */
    public function clearCache(): void
    {
        Cache::forget('site_feature_settings');
        Cache::forget('site_feature_group_homepage');
        Cache::forget('site_feature_group_product_page');

        $roles = ['admin', 'business', 'saler', 'verifier', 'customer'];
        foreach ($roles as $r) {
            Cache::forget("dashboard_menu_{$r}");
        }
    }
}
