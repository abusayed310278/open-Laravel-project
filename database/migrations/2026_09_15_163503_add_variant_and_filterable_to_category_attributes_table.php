<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_attributes', function (Blueprint $table) {
            $table->boolean('is_filterable')->default(false)->after('is_required');
            $table->boolean('is_variant')->default(false)->after('is_filterable');
        });
    }

    public function down(): void
    {
        Schema::table('category_attributes', function (Blueprint $table) {
            $table->dropColumn(['is_filterable', 'is_variant']);
        });
    }
};
