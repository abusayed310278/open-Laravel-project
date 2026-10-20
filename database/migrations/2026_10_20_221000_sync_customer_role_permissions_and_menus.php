<?php

use App\Models\DashboardMenu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Assign proper permission_slug to customer dashboard menus
        $updates = [
            'account.orders.index' => 'orders.manage',
            'account.invoices.index' => 'orders.manage',
            'account.returns.index' => 'orders.manage',
            'account.messages.index' => 'chat.access',
            'account.support.index' => 'support.manage',
            'account.reviews.index' => 'reviews.manage',
            'saler.products.index' => 'products.manage',
            'saler.categories.builder' => 'categories.manage',
            'saler.brands.index' => 'brands.manage',
            'saler.inventory.index' => 'inventory.manage',
            'saler.orders.index' => 'orders.manage',
            'saler.verification.index' => 'verifications.inspect',
            'saler.payouts.index' => 'payments.manage',
            'saler.store.edit' => 'products.manage',
            'saler.subscriptions.index' => 'subscriptions.manage',
            'saler.payment-settings.edit' => 'payments.manage',
            'saler.appointments.index' => 'verifications.inspect',
            'saler.warehouse.index' => 'inventory.manage',
            'saler.messages.index' => 'chat.access',
            'saler.support.index' => 'support.manage',
        ];

        foreach ($updates as $routeName => $permSlug) {
            DashboardMenu::where('route_name', $routeName)->update([
                'permission_slug' => $permSlug,
            ]);
        }

        // 2. Add missing seller management items to customer role menu tree
        $customerSellerItems = [
            [
                'title' => 'Sales Orders',
                'route_name' => 'saler.orders.index',
                'group_name' => 'CATALOG',
                'permission_slug' => 'orders.manage',
                'icon' => 'shopping-bag',
                'sort_order' => 6,
            ],
            [
                'title' => 'Verification Status',
                'route_name' => 'saler.verification.index',
                'group_name' => 'CATALOG',
                'permission_slug' => 'verifications.inspect',
                'icon' => 'shield',
                'sort_order' => 7,
            ],
            [
                'title' => 'Wallet & Payouts',
                'route_name' => 'saler.payouts.index',
                'group_name' => 'CATALOG',
                'permission_slug' => 'payments.manage',
                'icon' => 'wallet',
                'sort_order' => 8,
            ],
            [
                'title' => 'Store & Settings',
                'route_name' => 'saler.store.edit',
                'group_name' => 'CATALOG',
                'permission_slug' => 'products.manage',
                'icon' => 'cog',
                'sort_order' => 9,
            ],
        ];

        foreach ($customerSellerItems as $item) {
            DashboardMenu::updateOrCreate(
                [
                    'role_slug' => 'customer',
                    'route_name' => $item['route_name'],
                ],
                array_merge($item, [
                    'role_slug' => 'customer',
                    'is_enabled' => true,
                ])
            );
        }

        // 3. Keep customer & saler role permissions fully synchronized
        $custRole = Role::where('slug', 'customer')->first();
        $salerRole = Role::where('slug', 'saler')->first();

        if ($custRole && $salerRole) {
            // Give customer any extra permissions saler has (such as payments.manage, etc.)
            $mergedPermIds = $custRole->permissions()->pluck('permissions.id')
                ->merge($salerRole->permissions()->pluck('permissions.id'))
                ->unique()
                ->values()
                ->all();

            $custRole->permissions()->sync($mergedPermIds);
            $salerRole->permissions()->sync($mergedPermIds);
        }

        // 4. Clear all site management and permission caches
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
        // No destructive down operation needed
    }
};
