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
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('institution_id')->nullable();
            $table->string('lrn')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('ext_name')->nullable();;
            $table->string('gender');
            $table->date('birthdate');
            $table->timestamps();
        });
        
        Schema::create('student_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id');
            $table->foreignUuid('section_id')->references('id')->on('institution_sections');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
