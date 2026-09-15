<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes on plain string status columns that are filtered on
     * frequently across admin/seller queues and public storefront queries
     * but never got one from a `->constrained()` foreign key.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('publication_status');
            $table->index('status');
            $table->index('approval_status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_route');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('commission_records', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('seller_payouts', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('manual_payment_submissions', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['publication_status']);
            $table->dropIndex(['status']);
            $table->dropIndex(['approval_status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_route']);
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('commission_records', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('seller_payouts', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('manual_payment_submissions', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
