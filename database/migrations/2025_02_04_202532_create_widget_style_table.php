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
        Schema::create('widget_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('widget_id')->nullable()
                ->constrained('widgets')
                ->cascadeOnDelete();

            // Start Button Styles
            $table->integer('start_button_border_radius')->default(10);
            $table->string('start_button_background_color')->default('#000000');
            $table->string('start_button_text_color')->default('#FFFFFF');
            $table->string('start_button_hover_background_color')->default('#333333');
            $table->string('start_button_hover_text_color')->default('#FFFFFF');

            // Chat Button Styles
            $table->integer('chat_button_border_radius')->default(10);
            $table->string('chat_button_background_color')->default('#000000');
            $table->string('chat_button_text_color')->default('#FFFFFF');
            $table->string('chat_button_hover_background_color')->default('#333333');
            $table->string('chat_button_hover_text_color')->default('#FFFFFF');

            // Action Button Styles
            $table->integer('action_button_border_radius')->default(10);
            $table->string('action_button_background_color')->default('#000000');
            $table->string('action_button_text_color')->default('#FFFFFF');
            $table->string('action_button_hover_background_color')->default('#333333');
            $table->string('action_button_hover_text_color')->default('#FFFFFF');

            // Widget Container Styles
            $table->integer('widget_border_radius')->default(10);
            $table->string('widget_background_color_1')->default('#FFFFFF');
            $table->string('widget_background_color_2')->nullable();
            $table->string('widget_background_url')->nullable();
            $table->string('widget_text_color')->default('#000000');
            $table->string('widget_width')->default('300px');
            $table->string('widget_height')->default('500px');

            // Chat Bubble Styles
            $table->string('widget_agent_buble_background_color')->default('#F0F0F0');
            $table->string('widget_agent_buble_color')->default('#000000');
            $table->string('widget_user_buble_background_color')->default('#000000');
            $table->string('widget_user_buble_color')->default('#FFFFFF');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_styles');
    }
};
