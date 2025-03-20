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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->references('id')->on('students');
            $table->string('academic_year')->nullable();
            $table->mediumInteger('jan')->default(0);
            $table->mediumInteger('feb')->default(0);
            $table->mediumInteger('mar')->default(0);
            $table->mediumInteger('apr')->default(0);
            $table->mediumInteger('may')->default(0);
            $table->mediumInteger('jun')->default(0);
            $table->mediumInteger('jul')->default(0);
            $table->mediumInteger('aug')->default(0);
            $table->mediumInteger('sep')->default(0);
            $table->mediumInteger('oct')->default(0);
            $table->mediumInteger('nov')->default(0);
            $table->mediumInteger('dec')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
