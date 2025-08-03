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
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->required();
            $table->string('url')->required();
            $table->string('button_text')->required();
            $table->string('button_color')->required();
            $table->string('button_background_color')->required();
            $table->string('button_text_color')->required();
            $table->string('button_hover_color')->required();
            $table->string('button_hover_background_color')->required();
            $table->string('button_hover_text_color')->required();
            $table->string('button_border_radius')->required();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action');
    }
};
