<?php

namespace Database\Seeders;

use App\Models\DashboardMenu;
use App\Models\FeatureSetting;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RbacAndFeatureSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $rolesData = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full access to site management, settings, RBAC, users, and catalog control.', 'is_system' => true],
            ['name' => 'Store Owner', 'slug' => 'business', 'description' => 'Business vendor account with full storefront, warehouse, and seller product management.', 'is_system' => true],
            ['name' => 'Individual Seller', 'slug' => 'saler', 'description' => 'Individual seller listing products and requesting hardware verification appointments.', 'is_system' => true],
            ['name' => 'Verification Inspector', 'slug' => 'verifier', 'description' => 'Certified hardware inspector performing 40+ point physical quality checks.', 'is_system' => true],
            ['name' => 'Customer / User', 'slug' => 'customer', 'description' => 'Verified buyer browsing marketplace, managing orders, wishlist, and reviews.', 'is_system' => true],
        ];

        $roles = [];
        foreach ($rolesData as $r) {
            $roles[$r['slug']] = Role::updateOrCreate(['slug' => $r['slug']], $r);
        }

        // 2. Seed Permissions
        $permissionsData = [
            // Admin / Site Management
            ['name' => 'Manage Whole Site', 'slug' => 'site.manage', 'group' => 'system', 'description' => 'Full control over site toggles, roles, and dynamic dashboard menus.'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'group' => 'system', 'description' => 'View, edit, suspend, and delete platform users.'],
            ['name' => 'Manage Branding & Settings', 'slug' => 'settings.manage', 'group' => 'system', 'description' => 'Manage site logo, favicon, colors, mail, storage, and maintenance.'],
            ['name' => 'View Analytics & Visitor Reports', 'slug' => 'reports.view', 'group' => 'system', 'description' => 'Access live web traffic, visitors, and geographic reports.'],

            // Catalog & Inventory
            ['name' => 'Manage Catalog & Products', 'slug' => 'products.manage', 'group' => 'catalog', 'description' => 'Create, edit, publish, or delete products.'],
            ['name' => 'Manage Categories & Attributes', 'slug' => 'categories.manage', 'group' => 'catalog', 'description' => 'Create and customize product categories and attribute specs.'],
            ['name' => 'Manage Brands', 'slug' => 'brands.manage', 'group' => 'catalog', 'description' => 'Manage product manufacturer brands.'],
            ['name' => 'Manage Inventory', 'slug' => 'inventory.manage', 'group' => 'catalog', 'description' => 'Track stock levels, warehouse slots, and deposits.'],

            // Commerce & Orders
            ['name' => 'View & Process Orders', 'slug' => 'orders.manage', 'group' => 'commerce', 'description' => 'Process incoming orders, update status, and print invoices.'],
            ['name' => 'Manage Refunds & Payments', 'slug' => 'payments.manage', 'group' => 'commerce', 'description' => 'Verify offline manual payments, approve refunds, and request payouts.'],
            ['name' => 'Manage Subscriptions', 'slug' => 'subscriptions.manage', 'group' => 'commerce', 'description' => 'Manage recurring seller subscription plans.'],

            // Quality & Verification
            ['name' => 'Perform Hardware Inspections', 'slug' => 'verifications.inspect', 'group' => 'verification', 'description' => 'Inspect devices and issue Openbox certified grades.'],
            ['name' => 'Manage Verification Hubs & Team', 'slug' => 'verifications.manage', 'group' => 'verification', 'description' => 'Configure inspection locations, checklists, and verifier staff.'],

            // Messaging & Support
            ['name' => 'Access Chat Messenger', 'slug' => 'chat.access', 'group' => 'engagement', 'description' => 'Communicate with buyers, sellers, or support.'],
            ['name' => 'Manage Support Tickets', 'slug' => 'support.manage', 'group' => 'engagement', 'description' => 'Submit and resolve customer service tickets.'],
            ['name' => 'Manage Reviews & Ratings', 'slug' => 'reviews.manage', 'group' => 'engagement', 'description' => 'Write, reply to, or moderate customer reviews.'],
        ];

        $permissions = [];
        foreach ($permissionsData as $p) {
            $permissions[$p['slug']] = Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 3. Assign Role Permissions
        // Admin gets all permissions
        $roles['admin']->permissions()->sync(Permission::pluck('id')->toArray());

        // Business (Store Owner)
        $roles['business']->permissions()->sync(
            Permission::whereIn('slug', [
                'products.manage', 'categories.manage', 'brands.manage', 'inventory.manage',
                'orders.manage', 'payments.manage', 'chat.access', 'support.manage', 'reviews.manage'
            ])->pluck('id')->toArray()
        );

        // Saler (Individual Seller)
        $roles['saler']->permissions()->sync(
            Permission::whereIn('slug', [
                'products.manage', 'categories.manage', 'brands.manage', 'inventory.manage',
                'orders.manage', 'payments.manage', 'verifications.inspect', 'chat.access', 'support.manage', 'reviews.manage'
            ])->pluck('id')->toArray()
        );

        // Verifier
        $roles['verifier']->permissions()->sync(
            Permission::whereIn('slug', [
                'verifications.inspect', 'products.manage', 'chat.access', 'support.manage'
            ])->pluck('id')->toArray()
        );

        // Customer
        $roles['customer']->permissions()->sync(
            Permission::whereIn('slug', [
                'orders.manage', 'chat.access', 'support.manage', 'reviews.manage'
            ])->pluck('id')->toArray()
        );

        // 4. Feature Settings (Homepage & Product Detail Page Toggles)
        $featureSettings = [
            // Homepage Sections
            ['key' => 'home_hero_section', 'title' => 'Hero Banner & Search Tags', 'group' => 'homepage', 'description' => 'Top hero banner, promotional badge, main headline, and popular keyword tags.', 'is_enabled' => true, 'sort_order' => 1],
            ['key' => 'home_browse_categories', 'title' => 'Browse Categories Grid', 'group' => 'homepage', 'description' => 'Quick category shortcuts with icons and item counters.', 'is_enabled' => true, 'sort_order' => 2],
            ['key' => 'home_banners', 'title' => 'Promotional Banners Showcase', 'group' => 'homepage', 'description' => 'Dynamic marketing promo banners from database.', 'is_enabled' => true, 'sort_order' => 3],
            ['key' => 'home_featured_products', 'title' => 'Featured Products Section', 'group' => 'homepage', 'description' => 'Top performing products based on view count.', 'is_enabled' => true, 'sort_order' => 4],
            ['key' => 'home_refurbished_deals', 'title' => 'Certified Refurbished Deals', 'group' => 'homepage', 'description' => 'Pre-owned and refurbished graded items with discount tags.', 'is_enabled' => true, 'sort_order' => 5],
            ['key' => 'home_why_buy', 'title' => 'Why Buy on Openbox Section', 'group' => 'homepage', 'description' => '4 feature value-proposition cards (Verified Quality, 7-Day Return, Escrow, Delivery).', 'is_enabled' => true, 'sort_order' => 6],
            ['key' => 'home_openbox_guarantee', 'title' => 'The Openbox Guarantee Pillars', 'group' => 'homepage', 'description' => 'Diagnostic check, zero-risk replacement, and escrow checkout trust pillars.', 'is_enabled' => true, 'sort_order' => 7],
            ['key' => 'home_mobile_tech', 'title' => 'Mobile & Tech Showcase', 'group' => 'homepage', 'description' => 'Curated mobile phones and technology products grid.', 'is_enabled' => true, 'sort_order' => 8],
            ['key' => 'home_top_vendors', 'title' => 'Top Rated Vendors Grid', 'group' => 'homepage', 'description' => 'Verified store owners and top sellers showcase.', 'is_enabled' => true, 'sort_order' => 9],
            ['key' => 'home_start_selling_cta', 'title' => 'Start Selling Call-To-Action Banner', 'group' => 'homepage', 'description' => 'Banner driving seller registration and store opening.', 'is_enabled' => true, 'sort_order' => 10],
            ['key' => 'home_latest_drops', 'title' => 'Latest Drops Product Section', 'group' => 'homepage', 'description' => 'Newly published items on the marketplace.', 'is_enabled' => true, 'sort_order' => 11],
            ['key' => 'home_reviews', 'title' => 'What Our Community Says (Reviews)', 'group' => 'homepage', 'description' => 'Customer feedback and star rating cards.', 'is_enabled' => true, 'sort_order' => 12],
            ['key' => 'home_popular_searches', 'title' => 'Popular Searches Tag Cloud', 'group' => 'homepage', 'description' => 'SEO keyword tag buttons.', 'is_enabled' => true, 'sort_order' => 13],
            ['key' => 'home_newsletter', 'title' => 'Stay Updated Newsletter Subscribe', 'group' => 'homepage', 'description' => 'Email subscription newsletter signup form.', 'is_enabled' => true, 'sort_order' => 14],

            // Product Detail Page Sections
            ['key' => 'product_buy_box', 'title' => 'Price & Buy Box (Add to Cart / Checkout)', 'group' => 'product_page', 'description' => 'Product pricing, stock status, and primary action buttons.', 'is_enabled' => true, 'sort_order' => 1],
            ['key' => 'product_verification_badge', 'title' => 'Openbox Hardware Inspection Grade Badge', 'group' => 'product_page', 'description' => 'Certified diagnostic check and hardware grade badge (Grade A, B, C).', 'is_enabled' => true, 'sort_order' => 2],
            ['key' => 'product_specifications', 'title' => 'Specifications & Technical Attributes', 'group' => 'product_page', 'description' => 'Category-specific technical specifications table.', 'is_enabled' => true, 'sort_order' => 3],
            ['key' => 'product_seller_box', 'title' => 'Seller Info & Store Profile Card', 'group' => 'product_page', 'description' => 'Seller identity, store location, ratings, and vendor orders count.', 'is_enabled' => true, 'sort_order' => 4],
            ['key' => 'product_chat_widget', 'title' => 'Direct Message / Chat with Seller Button', 'group' => 'product_page', 'description' => 'Instant messenger trigger button on product page.', 'is_enabled' => true, 'sort_order' => 5],
            ['key' => 'product_reviews', 'title' => 'Customer Reviews & Star Ratings Section', 'group' => 'product_page', 'description' => 'Customer feedback and review submission form.', 'is_enabled' => true, 'sort_order' => 6],
            ['key' => 'product_related_items', 'title' => 'Related Products Recommendation Grid', 'group' => 'product_page', 'description' => 'Similar products recommendation cards.', 'is_enabled' => true, 'sort_order' => 7],
        ];

        foreach ($featureSettings as $fs) {
            FeatureSetting::updateOrCreate(['key' => $fs['key']], $fs);
        }

        // 5. Dynamic Dashboard Menus per Role
        // 5. Dynamic Dashboard Menus per Role
        $menuTree = [
            // Admin Menus
            'admin' => [
                ['title' => 'Dashboard', 'route_name' => 'admin.dashboard', 'group_name' => 'OVERVIEW', 'permission_slug' => 'site.manage', 'icon' => 'home', 'sort_order' => 1],
                ['title' => 'Manage Whole Site', 'route_name' => 'admin.site-management.index', 'group_name' => 'SITE CONTROL', 'permission_slug' => 'site.manage', 'icon' => 'sliders', 'sort_order' => 2],
                ['title' => 'User Directory', 'route_name' => 'admin.users.index', 'group_name' => 'SYSTEM & USERS', 'permission_slug' => 'users.manage', 'icon' => 'users', 'sort_order' => 3],
                ['title' => 'Branding & Settings', 'route_name' => 'admin.settings.logo', 'group_name' => 'SYSTEM & USERS', 'permission_slug' => 'settings.manage', 'icon' => 'cog', 'sort_order' => 4],
                ['title' => 'Visitor Reports', 'route_name' => 'admin.visitor-reports.index', 'group_name' => 'SYSTEM & USERS', 'permission_slug' => 'reports.view', 'icon' => 'chart', 'sort_order' => 5],
                ['title' => 'Products Catalog', 'route_name' => 'admin.products.index', 'group_name' => 'CATALOG', 'permission_slug' => 'products.manage', 'icon' => 'box', 'sort_order' => 6],
                ['title' => 'Categories Builder', 'route_name' => 'admin.categories.builder', 'group_name' => 'CATALOG', 'permission_slug' => 'categories.manage', 'icon' => 'sliders', 'sort_order' => 7],
                ['title' => 'Brands', 'route_name' => 'admin.brands.index', 'group_name' => 'CATALOG', 'permission_slug' => 'brands.manage', 'icon' => 'tag', 'sort_order' => 8],
                ['title' => 'Manage Inventory', 'route_name' => 'admin.inventory.index', 'group_name' => 'CATALOG', 'permission_slug' => 'inventory.manage', 'icon' => 'box', 'sort_order' => 9],
                ['title' => 'Orders List', 'route_name' => 'admin.orders.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'orders.manage', 'icon' => 'shopping-bag', 'sort_order' => 10],
                ['title' => 'Invoices', 'route_name' => 'admin.invoices.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'orders.manage', 'icon' => 'banknotes', 'sort_order' => 11],
                ['title' => 'Manual Payments', 'route_name' => 'admin.payment-verifications.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'payments.manage', 'icon' => 'credit-card', 'sort_order' => 12],
                ['title' => 'Payout Requests', 'route_name' => 'admin.payouts.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'payments.manage', 'icon' => 'wallet', 'sort_order' => 13],
                ['title' => 'Subscription Plans', 'route_name' => 'admin.subscriptions.plans.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'subscriptions.manage', 'icon' => 'credit-card', 'sort_order' => 14],
                ['title' => 'User KYC Verifications', 'route_name' => 'admin.verifications.index', 'group_name' => 'VERIFICATION & KYC', 'permission_slug' => 'verifications.manage', 'icon' => 'shield', 'sort_order' => 15],
                ['title' => 'KYC Requirements Setup', 'route_name' => 'admin.verification-requirements.index', 'group_name' => 'VERIFICATION & KYC', 'permission_slug' => 'verifications.manage', 'icon' => 'shield-check', 'sort_order' => 16],
                ['title' => 'Warehouses & Stock', 'route_name' => 'admin.warehouses.index', 'group_name' => 'VERIFICATION & KYC', 'permission_slug' => 'inventory.manage', 'icon' => 'building', 'sort_order' => 17],
                ['title' => 'Chat Messenger', 'route_name' => 'admin.chat.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'chat.access', 'icon' => 'chat', 'sort_order' => 18],
                ['title' => 'Support Tickets', 'route_name' => 'admin.support.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'support.manage', 'icon' => 'support', 'sort_order' => 19],
                ['title' => 'Reviews Moderation', 'route_name' => 'admin.reviews.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'reviews.manage', 'icon' => 'star', 'sort_order' => 20],
            ],

            // Business (Store Owner) Menus
            'business' => [
                ['title' => 'Dashboard', 'route_name' => 'business.dashboard', 'group_name' => 'OVERVIEW', 'permission_slug' => null, 'icon' => 'home', 'sort_order' => 1],
                ['title' => 'My Store Profile', 'route_name' => 'business.store.edit', 'group_name' => 'STORE', 'permission_slug' => null, 'icon' => 'building', 'sort_order' => 2],
                ['title' => 'Verification Status', 'route_name' => 'business.verification.index', 'group_name' => 'STORE', 'permission_slug' => null, 'icon' => 'shield', 'sort_order' => 3],
                ['title' => 'Subscription Plan', 'route_name' => 'business.subscription.index', 'group_name' => 'STORE', 'permission_slug' => null, 'icon' => 'credit-card', 'sort_order' => 4],
                ['title' => 'Products', 'route_name' => 'business.products.index', 'group_name' => 'CATALOG', 'permission_slug' => 'products.manage', 'icon' => 'box', 'sort_order' => 5],
                ['title' => 'Category Builder', 'route_name' => 'business.categories.builder', 'group_name' => 'CATALOG', 'permission_slug' => 'categories.manage', 'icon' => 'sliders', 'sort_order' => 6],
                ['title' => 'Brands', 'route_name' => 'business.brands.index', 'group_name' => 'CATALOG', 'permission_slug' => 'brands.manage', 'icon' => 'tag', 'sort_order' => 7],
                ['title' => 'Manage Inventory', 'route_name' => 'business.inventory.index', 'group_name' => 'CATALOG', 'permission_slug' => 'inventory.manage', 'icon' => 'box', 'sort_order' => 8],
                ['title' => 'Orders', 'route_name' => 'business.orders.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'orders.manage', 'icon' => 'shopping-bag', 'sort_order' => 9],
                ['title' => 'Invoices', 'route_name' => 'business.invoices.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'orders.manage', 'icon' => 'banknotes', 'sort_order' => 10],
                ['title' => 'Payment Settings', 'route_name' => 'business.payment-settings.edit', 'group_name' => 'COMMERCE', 'permission_slug' => 'payments.manage', 'icon' => 'credit-card', 'sort_order' => 11],
                ['title' => 'Payouts & Wallet', 'route_name' => 'business.payouts.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'payments.manage', 'icon' => 'wallet', 'sort_order' => 12],
                ['title' => 'Messages', 'route_name' => 'business.messages.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'chat.access', 'icon' => 'chat', 'sort_order' => 13],
                ['title' => 'Support Tickets', 'route_name' => 'business.support.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'support.manage', 'icon' => 'support', 'sort_order' => 14],
            ],

            // Saler (Individual Seller) Menus
            'saler' => [
                ['title' => 'Dashboard', 'route_name' => 'saler.dashboard', 'group_name' => 'OVERVIEW', 'permission_slug' => null, 'icon' => 'home', 'sort_order' => 1],
                ['title' => 'Seller Store Profile', 'route_name' => 'saler.store.edit', 'group_name' => 'PROFILE', 'permission_slug' => null, 'icon' => 'building', 'sort_order' => 2],
                ['title' => 'Verification Status', 'route_name' => 'saler.verification.index', 'group_name' => 'PROFILE', 'permission_slug' => null, 'icon' => 'shield', 'sort_order' => 3],
                ['title' => 'Products', 'route_name' => 'saler.products.index', 'group_name' => 'CATALOG', 'permission_slug' => 'products.manage', 'icon' => 'box', 'sort_order' => 4],
                ['title' => 'Category Builder', 'route_name' => 'saler.categories.builder', 'group_name' => 'CATALOG', 'permission_slug' => 'categories.manage', 'icon' => 'sliders', 'sort_order' => 5],
                ['title' => 'Brands', 'route_name' => 'saler.brands.index', 'group_name' => 'CATALOG', 'permission_slug' => 'brands.manage', 'icon' => 'tag', 'sort_order' => 6],
                ['title' => 'Manage Inventory', 'route_name' => 'saler.inventory.index', 'group_name' => 'CATALOG', 'permission_slug' => 'inventory.manage', 'icon' => 'box', 'sort_order' => 7],
                ['title' => 'Inspection Queue', 'route_name' => 'saler.appointments.index', 'group_name' => 'INSPECTIONS & DEPOSITS', 'permission_slug' => 'verifications.inspect', 'icon' => 'shield-check', 'sort_order' => 8],
                ['title' => 'Warehouse Deposit', 'route_name' => 'saler.warehouse.index', 'group_name' => 'INSPECTIONS & DEPOSITS', 'permission_slug' => 'inventory.manage', 'icon' => 'building', 'sort_order' => 9],
                ['title' => 'Orders', 'route_name' => 'saler.orders.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'orders.manage', 'icon' => 'shopping-bag', 'sort_order' => 10],
                ['title' => 'Payouts & Wallet', 'route_name' => 'saler.payouts.index', 'group_name' => 'COMMERCE', 'permission_slug' => 'payments.manage', 'icon' => 'wallet', 'sort_order' => 11],
                ['title' => 'Messages', 'route_name' => 'saler.messages.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'chat.access', 'icon' => 'chat', 'sort_order' => 12],
                ['title' => 'Support Tickets', 'route_name' => 'saler.support.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'support.manage', 'icon' => 'support', 'sort_order' => 13],
            ],

            // Verifier Menus
            'verifier' => [
                ['title' => 'Dashboard', 'route_name' => 'verifier.dashboard', 'group_name' => 'OVERVIEW', 'permission_slug' => null, 'icon' => 'home', 'sort_order' => 1],
                ['title' => 'Pending Queue', 'route_name' => 'verifier.queue.index', 'group_name' => 'INSPECTIONS', 'permission_slug' => 'verifications.inspect', 'icon' => 'shield-check', 'sort_order' => 2],
                ['title' => 'Inspection History', 'route_name' => 'verifier.history.index', 'group_name' => 'INSPECTIONS', 'permission_slug' => 'verifications.inspect', 'icon' => 'shield', 'sort_order' => 3],
                ['title' => 'Seller Products', 'route_name' => 'verifier.products.index', 'group_name' => 'CATALOG', 'permission_slug' => 'products.manage', 'icon' => 'box', 'sort_order' => 4],
                ['title' => 'Chat Messenger', 'route_name' => 'verifier.messages.index', 'group_name' => 'ENGAGEMENT', 'permission_slug' => 'chat.access', 'icon' => 'chat', 'sort_order' => 5],
            ],

            // Customer (User) Menus
            'customer' => [
                ['title' => 'Dashboard', 'route_name' => 'account.dashboard', 'group_name' => 'OVERVIEW', 'permission_slug' => null, 'icon' => 'home', 'sort_order' => 1],
                ['title' => 'My Orders', 'route_name' => 'account.orders.index', 'group_name' => 'SHOPPING', 'permission_slug' => 'orders.manage', 'icon' => 'shopping-bag', 'sort_order' => 2],
                ['title' => 'Wishlist', 'route_name' => 'account.wishlist.index', 'group_name' => 'SHOPPING', 'permission_slug' => null, 'icon' => 'heart', 'sort_order' => 3],
                ['title' => 'My Invoices', 'route_name' => 'account.invoices.index', 'group_name' => 'SHOPPING', 'permission_slug' => 'orders.manage', 'icon' => 'banknotes', 'sort_order' => 4],
                ['title' => 'Return Requests', 'route_name' => 'account.returns.index', 'group_name' => 'SHOPPING', 'permission_slug' => null, 'icon' => 'undo', 'sort_order' => 5],
                ['title' => 'Saved Addresses', 'route_name' => 'account.addresses.index', 'group_name' => 'ACCOUNT', 'permission_slug' => null, 'icon' => 'map-pin', 'sort_order' => 6],
                ['title' => 'Update Profile', 'route_name' => 'account.profile.edit', 'group_name' => 'ACCOUNT', 'permission_slug' => null, 'icon' => 'users', 'sort_order' => 7],
                ['title' => 'Chat Messages', 'route_name' => 'account.messages.index', 'group_name' => 'SUPPORT & FEEDBACK', 'permission_slug' => 'chat.access', 'icon' => 'chat', 'sort_order' => 8],
                ['title' => 'Support Tickets', 'route_name' => 'account.support.index', 'group_name' => 'SUPPORT & FEEDBACK', 'permission_slug' => 'support.manage', 'icon' => 'support', 'sort_order' => 9],
                ['title' => 'My Reviews', 'route_name' => 'account.reviews.index', 'group_name' => 'SUPPORT & FEEDBACK', 'permission_slug' => 'reviews.manage', 'icon' => 'star', 'sort_order' => 10],
            ],
        ];

        foreach ($menuTree as $roleSlug => $menus) {
            foreach ($menus as $m) {
                DashboardMenu::updateOrCreate(
                    [
                        'role_slug' => $roleSlug,
                        'title' => $m['title'],
                    ],
                    array_merge($m, ['role_slug' => $roleSlug, 'is_enabled' => true])
                );
            }
        }
    }
}
