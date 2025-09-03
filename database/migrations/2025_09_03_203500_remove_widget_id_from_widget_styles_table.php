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
            $table->dropForeign(['widget_id']);
            $table->dropColumn('widget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widget_styles', function (Blueprint $table) {
            $table->foreignId('widget_id')->nullable()
                ->constrained('widgets')
                ->cascadeOnDelete();
        });
    }
};
