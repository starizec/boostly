<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::statement('
            UPDATE contacts
            INNER JOIN chats ON chats.contact_id = contacts.id
            INNER JOIN widgets ON widgets.id = chats.widget_id
            SET contacts.user_id = widgets.user_id
            WHERE contacts.user_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
