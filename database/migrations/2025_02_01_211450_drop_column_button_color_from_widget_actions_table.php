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
            $table->dropColumn('button_hover_color');
            $table->dropColumn('button_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_actions', function (Blueprint $table) {
            $table->dropColumn('button_hover_color');
            $table->dropColumn('button_color');
        });
    }
};
