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
        Schema::disableForeignKeyConstraints();

        Schema::create('widget_actions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->required();
            $table->string('url')->required();
            $table->string('text')->required();
            $table->string('background_color')->required();
            $table->string('text_color')->required();
            $table->string('border_color')->required();
            $table->string('border_radius')->required();
            $table->foreignId('company_id')->constrained('companies')->required();
            $table->foreignId('user_id')->constrained('users')->required();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_actions');
    }
};
