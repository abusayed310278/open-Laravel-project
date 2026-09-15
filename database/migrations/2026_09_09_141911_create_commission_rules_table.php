<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('commission_type');
            $table->decimal('value', 8, 4);
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
