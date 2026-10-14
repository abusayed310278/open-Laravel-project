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
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->unsignedSmallInteger('badge_font_size')->nullable()->after('badge_text_color');
            $table->unsignedSmallInteger('title_line1_font_size')->nullable()->after('title_line1_color');
            $table->unsignedSmallInteger('title_line2_font_size')->nullable()->after('title_line2_color');
            $table->unsignedSmallInteger('subtitle_font_size')->nullable()->after('subtitle_color');
            $table->unsignedSmallInteger('callout_font_size')->nullable()->after('callout_accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn([
                'badge_font_size',
                'title_line1_font_size',
                'title_line2_font_size',
                'subtitle_font_size',
                'callout_font_size',
            ]);
        });
    }
};
