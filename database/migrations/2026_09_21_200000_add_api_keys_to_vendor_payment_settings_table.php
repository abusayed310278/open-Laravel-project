<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_payment_settings', function (Blueprint $table) {
            $table->text('stripe_publishable_key')->nullable()->after('stripe_enabled');
            $table->text('stripe_secret_key')->nullable()->after('stripe_publishable_key');
            $table->text('paypal_client_id')->nullable()->after('paypal_enabled');
            $table->text('paypal_client_secret')->nullable()->after('paypal_client_id');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_payment_settings', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_publishable_key',
                'stripe_secret_key',
                'paypal_client_id',
                'paypal_client_secret',
            ]);
        });
    }
};
