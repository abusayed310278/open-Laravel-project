<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('short_description')->nullable();

            $table->string('condition')->default('new');
            $table->string('grade')->default('ungraded');
            $table->text('grade_notes')->nullable();

            $table->string('status')->default('draft');
            $table->string('approval_status')->default('pending');
            $table->string('publication_status')->default('unpublished');
            $table->string('verification_status')->default('not_requested');
            $table->string('warehouse_status')->default('not_deposited');
            $table->string('payment_route')->default('seller');
            $table->string('rejection_reason')->nullable();

            $table->decimal('price', 12, 2);
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_negotiable')->default(false);

            $table->decimal('weight', 8, 2)->nullable();
            $table->string('shipping_type')->default('free');
            $table->decimal('shipping_flat_rate', 8, 2)->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
