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
        Schema::create('institutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('abbr')->nullable();
            $table->string('address')->nullable();
            $table->string('division')->nullable();
            $table->string('region')->nullable();
            $table->string('gov_id')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
        
        Schema::create('user_institutions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('UUID()'));
            $table->foreignUuid('user_id');
            $table->foreignUuid('institution_id');
            $table->smallInteger('is_default')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
