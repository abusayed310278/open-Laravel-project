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
            $table->boolean('enable_overlay')->default(false)->after('image');
            $table->string('badge_text', 150)->nullable()->after('enable_overlay');
            $table->string('badge_bg_color', 30)->nullable()->after('badge_text');
            $table->string('badge_text_color', 30)->nullable()->after('badge_bg_color');
            $table->string('title_line1', 150)->nullable()->after('badge_text_color');
            $table->string('title_line1_color', 30)->nullable()->after('title_line1');
            $table->string('title_line2', 150)->nullable()->after('title_line1_color');
            $table->string('title_line2_color', 30)->nullable()->after('title_line2');
            $table->text('subtitle')->nullable()->after('title_line2_color');
            $table->string('subtitle_color', 30)->nullable()->after('subtitle');
            $table->text('callout_text')->nullable()->after('subtitle_color');
            $table->string('callout_color', 30)->nullable()->after('callout_text');
            $table->string('callout_accent_color', 30)->nullable()->after('callout_color');
            $table->json('features')->nullable()->after('callout_accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn([
                'enable_overlay',
                'badge_text',
                'badge_bg_color',
                'badge_text_color',
                'title_line1',
                'title_line1_color',
                'title_line2',
                'title_line2_color',
                'subtitle',
                'subtitle_color',
                'callout_text',
                'callout_color',
                'callout_accent_color',
                'features',
            ]);
        });
    }
};
