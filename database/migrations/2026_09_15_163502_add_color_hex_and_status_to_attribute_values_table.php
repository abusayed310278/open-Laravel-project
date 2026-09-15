<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->string('color_hex', 20)->nullable()->after('slug');
            $table->boolean('is_active')->default(true)->after('sort_order');

            $table->index(['attribute_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropIndex(['attribute_id', 'is_active', 'sort_order']);
            $table->dropColumn(['color_hex', 'is_active']);
        });
    }
};
