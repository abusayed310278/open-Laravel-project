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
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('reviewer_id')->nullable()->change();
            $table->foreignId('order_id')->nullable()->change();
            $table->string('reviewable_type')->nullable()->change();
            $table->unsignedBigInteger('reviewable_id')->nullable()->change();
            $table->string('reviewer_name')->nullable()->after('reviewer_id');
            $table->string('city')->nullable()->after('reviewer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['reviewer_name', 'city']);
            $table->foreignId('reviewer_id')->nullable(false)->change();
            $table->foreignId('order_id')->nullable(false)->change();
            $table->string('reviewable_type')->nullable(false)->change();
            $table->unsignedBigInteger('reviewable_id')->nullable(false)->change();
        });
    }
};
