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
        Schema::table('chat_messages', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['agent_id']);
            
            // Add the new foreign key constraint that references users table
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Drop the users foreign key constraint
            $table->dropForeign(['agent_id']);
            
            // Restore the original agents foreign key constraint
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }
};
