<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_product_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->foreignId('received_by')->constrained('users');
            $table->text('condition_notes')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_receipts');
    }
};
