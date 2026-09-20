<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DashboardMenu;
use App\Models\FeatureSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Services\SiteManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteManagementController extends Controller
{
    public function __construct(
        private readonly SiteManagementService $siteService
    ) {}

    /**
     * Site Management Overview
     */
    public function index(): View
    {
        $homepageFeaturesCount = FeatureSetting::group('homepage')->count();
        $homepageActiveCount = FeatureSetting::group('homepage')->where('is_enabled', true)->count();
        $productPageFeaturesCount = FeatureSetting::group('product_page')->count();
        $productPageActiveCount = FeatureSetting::group('product_page')->where('is_enabled', true)->count();
        $rolesCount = Role::count();
        $permissionsCount = Permission::count();
        $menusCount = DashboardMenu::count();

        return view('admin.site-management.index', compact(
            'homepageFeaturesCount',
            'homepageActiveCount',
            'productPageFeaturesCount',
            'productPageActiveCount',
            'rolesCount',
            'permissionsCount',
            'menusCount'
        ));
    }

    /**
     * Manage Feature Toggles (Homepage & Product Detail Page)
     */
    public function features(Request $request): View
    {
        $group = $request->query('group', 'homepage');
        $features = FeatureSetting::group($group)->ordered()->get();

        return view('admin.site-management.features', compact('features', 'group'));
    }

    /**
     * Toggle Feature On/Off
     */
    public function toggleFeature(Request $request, string $key): RedirectResponse
    {
        $newState = $this->siteService->toggleFeature($key);
        $feature = FeatureSetting::where('key', $key)->first();
        $stateText = $newState ? 'enabled' : 'disabled';

        return redirect()->back()->with('status', "Section '{$feature->title}' has been {$stateText}.");
    }

    /**
     * Update Feature Setting details
     */
    public function updateFeature(Request $request, FeatureSetting $feature): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $this->siteService->updateFeature($feature->id, $validated);

        return redirect()->back()->with('status', "Feature '{$feature->title}' updated successfully.");
    }

    /**
     * Manage Roles & Permission Matrix
     */
    public function roles(): View
    {
        $roles = Role::with('permissions')->get();
        $permissionsGrouped = Permission::all()->groupBy('group');

        return view('admin.site-management.roles', compact('roles', 'permissionsGrouped'));
    }

    /**
     * Update Role Permissions Matrix
     */
    public function updateRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionIds = $validated['permissions'] ?? [];
        $this->siteService->updateRolePermissions($role->id, $permissionIds);

        return redirect()->back()->with('status', "Permissions for role '{$role->name}' updated successfully.");
    }

    /**
     * Manage Dynamic Dashboard Menus per Role
     */
    public function menus(Request $request): View
    {
        $activeRoleSlug = $request->query('role', 'customer');
        $roles = Role::all();
        $menus = DashboardMenu::forRole($activeRoleSlug)->ordered()->get();
        $permissions = Permission::ordered()->get();

        return view('admin.site-management.menus', compact('roles', 'menus', 'activeRoleSlug', 'permissions'));
    }

    /**
     * Create Dashboard Menu Item
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_slug' => ['required', 'string', 'exists:roles,slug'],
            'title' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:150'],
            'url_path' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'permission_slug' => ['nullable', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->siteService->createDashboardMenu($validated);

        return redirect()->route('admin.site-management.menus', ['role' => $validated['role_slug']])
            ->with('status', "Menu item '{$validated['title']}' created successfully.");
    }

    /**
     * Update Dashboard Menu Item
     */
    public function updateMenu(Request $request, DashboardMenu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:100'],
            'permission_slug' => ['nullable', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $this->siteService->updateDashboardMenu($menu->id, $validated);

        return redirect()->route('admin.site-management.menus', ['role' => $menu->role_slug])
            ->with('status', "Menu item '{$menu->title}' updated successfully.");
    }

    /**
     * Delete Dashboard Menu Item
     */
    public function deleteMenu(DashboardMenu $menu): RedirectResponse
    {
        $roleSlug = $menu->role_slug;
        $title = $menu->title;
        $this->siteService->deleteDashboardMenu($menu->id);

        return redirect()->route('admin.site-management.menus', ['role' => $roleSlug])
            ->with('status', "Menu item '{$title}' deleted successfully.");
    }

    /**
     * Manual Cache Clear Trigger
     */
    public function clearCache(): RedirectResponse
    {
        $this->siteService->clearCache();

        return redirect()->back()->with('status', 'Site management cache cleared successfully.');
    }
}
