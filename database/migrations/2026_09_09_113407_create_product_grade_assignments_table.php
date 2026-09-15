<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_grade_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_verification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verifier_id')->constrained('users');
            $table->string('grade');
            $table->text('grade_notes')->nullable();
            $table->unsignedTinyInteger('battery_health')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_grade_assignments');
    }
};
