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
        Schema::table('widget_styles', function (Blueprint $table) {
            $table->string('chat_header_background_color')->nullable();
            $table->string('chat_header_text_color')->nullable();
            $table->string('chat_header_background_image')->nullable();
            $table->string('chat_body_background_color')->nullable();
            $table->string('chat_body_text_color')->nullable();
            $table->string('chat_body_background_image')->nullable();
            $table->string('chat_footer_background_color')->nullable();
            $table->string('chat_footer_text_color')->nullable();
            $table->string('chat_footer_background_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_styles', function (Blueprint $table) {
            $table->dropColumn('chat_header_background_color');
            $table->dropColumn('chat_header_text_color');
            $table->dropColumn('chat_header_background_image');
            $table->dropColumn('chat_body_background_color');
            $table->dropColumn('chat_body_text_color');
            $table->dropColumn('chat_body_background_image');
            $table->dropColumn('chat_footer_background_color');
            $table->dropColumn('chat_footer_text_color');
        });
    }
};
