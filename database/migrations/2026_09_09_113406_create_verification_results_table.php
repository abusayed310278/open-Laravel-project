<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_verification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('verification_checklists')->cascadeOnDelete();
            $table->string('result')->default('na');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_verification_id', 'checklist_item_id'], 'verification_results_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_results');
    }
};
