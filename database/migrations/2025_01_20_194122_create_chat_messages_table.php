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

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->string('type')->default('text');
            $table->boolean('is_read')->default(false);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
