<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('document_type');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['role', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_requirements');
    }
};
