<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('stripe_enabled')->default(false);
            $table->boolean('paypal_enabled')->default(false);
            $table->boolean('cod_enabled')->default(true);
            $table->boolean('manual_bank_enabled')->default(false);
            $table->json('bank_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payment_settings');
    }
};
