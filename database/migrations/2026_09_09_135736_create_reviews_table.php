<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('reviewable_type');
            $table->unsignedBigInteger('reviewable_id');
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id']);
            $table->unique(['reviewer_id', 'order_id', 'reviewable_type', 'reviewable_id'], 'reviews_unique_per_order_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
