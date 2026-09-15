<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifier_profiles', function (Blueprint $table) {
            $table->foreign('assigned_location_id')->references('id')->on('verification_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('verifier_profiles', function (Blueprint $table) {
            $table->dropForeign(['assigned_location_id']);
        });
    }
};
