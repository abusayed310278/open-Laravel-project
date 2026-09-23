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
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->json('business_hours')->nullable()->after('social_links');
            $table->string('timezone')->nullable()->after('business_hours');
        });

        Schema::table('saler_profiles', function (Blueprint $table) {
            $table->json('social_links')->nullable()->after('country');
            $table->json('business_hours')->nullable()->after('social_links');
            $table->string('timezone')->nullable()->after('business_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn(['business_hours', 'timezone']);
        });

        Schema::table('saler_profiles', function (Blueprint $table) {
            $table->dropColumn(['social_links', 'business_hours', 'timezone']);
        });
    }
};
