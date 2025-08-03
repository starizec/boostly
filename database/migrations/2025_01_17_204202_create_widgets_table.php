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

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->required();
            $table->boolean('form_active')->required();
            $table->boolean('form_show_name')->required();
            $table->boolean('form_show_email')->required();
            $table->boolean('form_show_message')->required();
            $table->boolean('show_monday')->default(true);
            $table->boolean('show_tuesday')->default(true);
            $table->boolean('show_wednesday')->default(true);
            $table->boolean('show_thursday')->default(true);
            $table->boolean('show_friday')->default(true);
            $table->boolean('show_saturday')->default(true);
            $table->boolean('show_sunday')->default(true);
            $table->time('show_time_start')->default('00:00:00');
            $table->time('show_time_end')->default('23:59:59');
            $table->text('offline_message')->nullable();
            $table->string('send_to_email')->nullable();
            $table->foreignId('action_id')->constrained('widget_actions')->required();
            $table->foreignId('media_id')->constrained('media')->required();
            $table->foreignId('company_id')->constrained('companies')->required();
            $table->foreignId('user_id')->constrained('users')->required();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
