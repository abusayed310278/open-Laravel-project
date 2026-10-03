<?php

use App\Models\DashboardMenu;
use App\Services\SiteManagementService;
use Database\Seeders\CmsPagesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('dashboard_menus')) {
            $menus = [
                [
                    'role_slug' => 'admin',
                    'route_name' => 'admin.blog.comments.index',
                    'title' => 'Blog Comments',
                    'group_name' => 'CONTENT',
                    'permission_slug' => 'site.manage',
                    'icon' => 'chat',
                    'sort_order' => 21,
                    'is_enabled' => true,
                ],
                [
                    'role_slug' => 'admin',
                    'route_name' => 'admin.pages.index',
                    'title' => 'Pages',
                    'group_name' => 'CONTENT',
                    'permission_slug' => 'site.manage',
                    'icon' => 'document',
                    'sort_order' => 22,
                    'is_enabled' => true,
                ],
                [
                    'role_slug' => 'admin',
                    'route_name' => 'admin.blog.index',
                    'title' => 'Blog Posts',
                    'group_name' => 'CONTENT',
                    'permission_slug' => 'site.manage',
                    'icon' => 'newspaper',
                    'sort_order' => 23,
                    'is_enabled' => true,
                ],
            ];

            foreach ($menus as $item) {
                DashboardMenu::updateOrCreate(
                    [
                        'role_slug' => $item['role_slug'],
                        'route_name' => $item['route_name'],
                    ],
                    $item
                );
            }
        }

        // Ensure CMS pages (Help Center, Privacy Policy, Terms, Delete Policy, etc.) exist
        if (Schema::hasTable('pages')) {
            try {
                (new CmsPagesSeeder())->run();
            } catch (\Throwable $e) {
                // Ignore if seeder encounters duplicate or issue
            }
        }

        // Flush cached navigation
        try {
            if (app()->bound(SiteManagementService::class)) {
                app(SiteManagementService::class)->clearCache();
            }
            Cache::forget('dashboard_menu_admin');
        } catch (\Throwable $e) {
            // Safe to ignore in deployment CLI context
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('dashboard_menus')) {
            DashboardMenu::where('role_slug', 'admin')
                ->whereIn('route_name', [
                    'admin.blog.comments.index',
                    'admin.pages.index',
                    'admin.blog.index',
                ])
                ->delete();
        }

        try {
            if (app()->bound(SiteManagementService::class)) {
                app(SiteManagementService::class)->clearCache();
            }
            Cache::forget('dashboard_menu_admin');
        } catch (\Throwable $e) {
        }
    }
};
