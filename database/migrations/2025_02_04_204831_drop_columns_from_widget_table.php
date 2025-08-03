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
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropColumn([
                'button_text_color',
                'button_background_color',
                'button_text_hover_color',
                'button_background_hover_color',
                'widget_border_radius',
                'widget_height',
                'widget_width'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->string('button_text_color')->nullable();
            $table->string('button_background_color')->nullable();
            $table->string('button_text_hover_color')->nullable();
            $table->string('button_background_hover_color')->nullable();
            $table->integer('widget_border_radius')->nullable();
            $table->string('widget_height')->nullable();
            $table->string('widget_width')->nullable();
        });
    }
};
