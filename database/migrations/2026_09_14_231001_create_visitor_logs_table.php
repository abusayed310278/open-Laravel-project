<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('url', 255)->index();
            $table->string('referrer', 500)->nullable();
            $table->string('device_type', 20)->default('desktop')->index(); // desktop, mobile, tablet, bot
            $table->string('platform', 50)->nullable()->index(); // Windows, OS X, Android, iOS, Linux, etc.
            $table->string('browser', 50)->nullable()->index(); // Chrome, Safari, Firefox, Edge, Opera, etc.
            $table->string('country', 100)->nullable()->index();
            $table->string('country_code', 10)->nullable()->index();
            $table->string('city', 100)->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
