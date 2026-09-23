<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saler_profiles', function (Blueprint $table) {
            $table->string('state')->nullable()->after('city');
        });

        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('state')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('saler_profiles', function (Blueprint $table) {
            $table->dropColumn('state');
        });

        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
