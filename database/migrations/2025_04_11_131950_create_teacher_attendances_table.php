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
        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institution_id')->references('id')->on('institutions');
            $table->string('employee_id')->nullable();
            $table->string('employee')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->date('auth_date')->nullable();
            $table->time('auth_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
    }
};
