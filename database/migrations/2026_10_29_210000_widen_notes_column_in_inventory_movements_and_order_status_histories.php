<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->text('notes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('notes')->nullable()->change();
        });

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->string('notes')->nullable()->change();
        });
    }
};

