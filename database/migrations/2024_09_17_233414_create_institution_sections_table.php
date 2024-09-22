<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institution_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('class_adviser')->references('id')->on('users')->nullable();
            $table->foreignUuid('institution_id');
            $table->string('grade_level');
            $table->string('title');
            $table->string('academic_year');
            $table->timestamps();
        });
        
        Schema::create('student_sections', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()'));
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
        Schema::dropIfExists('institution_sections');
    }
};
