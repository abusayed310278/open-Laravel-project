<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users');
            $table->decimal('sale_amount', 12, 2);
            $table->decimal('commission_rate', 8, 4);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('seller_amount', 12, 2);
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_records');
    }
};
