<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widget_styles', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::statement('
            UPDATE widget_styles
            INNER JOIN widgets ON widgets.style_id = widget_styles.id
            SET widget_styles.user_id = widgets.user_id
            WHERE widget_styles.user_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('widget_styles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
