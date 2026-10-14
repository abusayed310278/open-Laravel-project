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
        Schema::table('products', function (Blueprint $table) {
            $table->string('promo_code')->nullable()->after('compare_price');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('promo_code');
            $table->boolean('allow_coupons')->default(true)->after('discount_percent');
            $table->boolean('allow_gift_cards')->default(true)->after('allow_coupons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['promo_code', 'discount_percent', 'allow_coupons', 'allow_gift_cards']);
        });
    }
};
