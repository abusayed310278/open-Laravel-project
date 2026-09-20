<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 2. Permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role Permissions pivot table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Feature Settings table (homepage, product detail, global toggles)
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('group')->default('homepage'); // homepage, product_page, global
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });

        // 5. Dashboard Menus table (dynamic sidebar builder)
        Schema::create('dashboard_menus', function (Blueprint $table) {
            $table->id();
            $table->string('role_slug'); // customer, business, saler, verifier, admin
            $table->string('title');
            $table->string('route_name')->nullable();
            $table->string('url_path')->nullable();
            $table->string('icon')->nullable();
            $table->text('icon_svg')->nullable();
            $table->string('group_name')->nullable(); // Section header (e.g. CATALOG & SELLERS)
            $table->string('permission_slug')->nullable(); // Permission required to view
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Optional role_id foreign key on users table
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }

        Schema::dropIfExists('dashboard_menus');
        Schema::dropIfExists('feature_settings');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
