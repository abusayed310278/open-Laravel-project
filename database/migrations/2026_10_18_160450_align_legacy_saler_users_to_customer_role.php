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
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'saler')
            ->update(['role' => 'customer']);

        \Illuminate\Support\Facades\DB::table('saler_profiles')
            ->update(['is_store_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep as customer
    }
};
