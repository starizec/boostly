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

        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('phone_number')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('users', function (Blueprint $table) {
            // Drop the columns added in up()
            $table->dropColumn([
                'phone_number',
                'role',
                'last_login_at'
            ]);
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::enableForeignKeyConstraints();
    }
};
