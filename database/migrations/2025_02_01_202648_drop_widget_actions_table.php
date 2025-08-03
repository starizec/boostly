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
        // First drop the foreign key constraint from widgets table
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropForeign(['action_id']);
            $table->dropColumn('action_id');
        });

        // Then drop the widget_actions table
        Schema::dropIfExists('widget_actions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the widget_actions table
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

        // Add back the action_id column and foreign key constraint to widgets table
        Schema::table('widgets', function (Blueprint $table) {
            $table->foreignId('action_id')->constrained('widget_actions')->required();
        });
    }
};
