<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->foreignId('attribute_group_id')->nullable()->after('id')->constrained('attribute_groups')->nullOnDelete();
            $table->string('placeholder')->nullable()->after('unit');
            $table->boolean('is_required')->default(false)->after('is_filterable');
            $table->boolean('is_variant')->default(false)->after('is_required');
            $table->boolean('is_active')->default(true)->after('is_variant');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');

            $table->index(['is_active', 'sort_order']);
            $table->index('attribute_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_group_id']);
            $table->dropIndex(['is_active', 'sort_order']);
            $table->dropIndex(['attribute_group_id']);
            $table->dropColumn(['attribute_group_id', 'placeholder', 'is_required', 'is_variant', 'is_active', 'sort_order']);
        });
    }
};
