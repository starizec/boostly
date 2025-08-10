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
        Schema::table('chats', function (Blueprint $table) {
            // Drop the existing status enum column
            $table->dropColumn('status');
            
            // Add the new status_id foreign key column
            $table->foreignId('status_id')->nullable()->constrained('statuses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            // Drop the status_id foreign key column
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            
            // Recreate the original status enum column
            $table->enum('status', ['active', 'archived'])->default('active');
        });
    }
};
