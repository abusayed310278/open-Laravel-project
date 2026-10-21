<?php

use App\Models\DashboardMenu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DashboardMenu::updateOrCreate(
            [
                'role_slug' => 'customer',
                'route_name' => 'saler.subscriptions.index',
            ],
            [
                'title' => 'Subscription Plan',
                'group_name' => 'CATALOG',
                'permission_slug' => 'subscriptions.manage',
                'icon' => 'credit-card',
                'sort_order' => 10,
                'is_enabled' => true,
            ]
        );

        $roles = ['admin', 'business', 'saler', 'verifier', 'customer', 'user', 'seller'];
        foreach ($roles as $r) {
            Cache::forget("dashboard_menu_{$r}");
            Cache::forget("role_permissions_{$r}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DashboardMenu::where('role_slug', 'customer')
            ->where('route_name', 'saler.subscriptions.index')
            ->delete();

        Cache::forget('dashboard_menu_customer');
    }
};
