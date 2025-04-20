+<?php

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
        Schema::create('institution_time_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->foreignUuid('institution_id')->references('id')->on('institutions');
            $table->time('start_working_time')->nullable();
            $table->time('end_working_time')->nullable();
            $table->time('early_time_in')->nullable();
            $table->time('late_time_in')->nullable();
            $table->time('break_in')->nullable();
            $table->time('break_out')->nullable();
            $table->time('valid_check_out')->nullable();
            $table->time('late_check_out')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_time_schedule');
    }
};
