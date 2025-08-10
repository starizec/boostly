<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        
        // Get the foreign key constraint names
        $foreignKeys = $this->getForeignKeys('chat_tag');
        
        // Drop all foreign key constraints
        foreach ($foreignKeys as $foreignKey) {
            Schema::table('chat_tag', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }
        
        Schema::table('chat_tag', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['chat_id', 'chat_tag_id']);
        });
        
        Schema::table('chat_tag', function (Blueprint $table) {
            // Rename the column from chat_tag_id to tag_id
            $table->renameColumn('chat_tag_id', 'tag_id');
        });
        
        Schema::table('chat_tag', function (Blueprint $table) {
            // Add the unique constraint back with the new column name
            $table->unique(['chat_id', 'tag_id']);
        });
        
        // Recreate foreign key constraints with the new column name
        Schema::table('chat_tag', function (Blueprint $table) {
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        
        // Get the foreign key constraint names
        $foreignKeys = $this->getForeignKeys('chat_tag');
        
        // Drop all foreign key constraints
        foreach ($foreignKeys as $foreignKey) {
            Schema::table('chat_tag', function (Blueprint $table) use ($foreignKey) {
                $table->dropForeign($foreignKey);
            });
        }
        
        Schema::table('chat_tag', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['chat_id', 'tag_id']);
        });
        
        Schema::table('chat_tag', function (Blueprint $table) {
            // Rename the column back from tag_id to chat_tag_id
            $table->renameColumn('tag_id', 'chat_tag_id');
        });
        
        Schema::table('chat_tag', function (Blueprint $table) {
            // Add the unique constraint back with the original column name
            $table->unique(['chat_id', 'chat_tag_id']);
        });
        
        // Recreate foreign key constraints with the original column name
        Schema::table('chat_tag', function (Blueprint $table) {
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('chat_tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
        
        Schema::enableForeignKeyConstraints();
    }
    
    /**
     * Get foreign key constraint names for a table
     */
    private function getForeignKeys(string $tableName): array
    {
        $foreignKeys = [];
        
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '{$tableName}' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        foreach ($constraints as $constraint) {
            $foreignKeys[] = $constraint->CONSTRAINT_NAME;
        }
        
        return $foreignKeys;
    }
};
