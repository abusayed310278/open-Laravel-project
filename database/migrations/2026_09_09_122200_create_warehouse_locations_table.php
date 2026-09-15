<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('zone', 20);
            $table->string('row', 20);
            $table->string('shelf', 20);
            $table->string('slot', 20);
            $table->boolean('is_occupied')->default(false);
            $table->timestamps();

            $table->unique(['warehouse_id', 'zone', 'row', 'shelf', 'slot'], 'warehouse_locations_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_locations');
    }
};
