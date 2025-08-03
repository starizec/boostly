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
        Schema::table('widget_actions', function (Blueprint $table) {
            $table->dropColumn([
                'button_background_color',
                'button_text_color', 
                'button_hover_background_color',
                'button_hover_text_color',
                'button_border_radius'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_actions', function (Blueprint $table) {
            $table->string('button_background_color')->nullable();
            $table->string('button_text_color')->nullable();
            $table->string('button_hover_background_color')->nullable();
            $table->string('button_hover_text_color')->nullable();
            $table->integer('button_border_radius')->nullable();
        });
    }
};
