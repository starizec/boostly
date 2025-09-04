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
        Schema::table('widgets', function (Blueprint $table) {
            $table->text('form_title')->nullable();
            $table->text('form_message')->nullable();
            $table->text('offline_title')->nullable();
            $table->text('form_placeholder_name')->nullable()->default('Name');
            $table->text('form_placeholder_email')->nullable()->default('Email');
            $table->text('form_placeholder_message')->nullable()->default('Message');
            $table->text('message_input_placeholder')->nullable()->default('Write your message...');
            $table->text('back_button_text')->nullable()->default('Back');
            $table->text('send_button_text')->nullable()->default('Send');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropColumn('form_title');
            $table->dropColumn('form_message');
            $table->dropColumn('offline_title');
            $table->dropColumn('form_placeholder_name');
            $table->dropColumn('form_placeholder_email');
            $table->dropColumn('form_placeholder_message');
            $table->dropColumn('message_input_placeholder');
            $table->dropColumn('back_button_text');
            $table->dropColumn('send_button_text');
        });
    }
};
