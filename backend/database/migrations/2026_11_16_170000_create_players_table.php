<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('full_name', 200);
            $table->string('fide_id', 20)->nullable()->unique();
            $table->string('federation_code', 3)->nullable();
            $table->string('title', 12)->default('none');
            $table->unsignedSmallInteger('standard_rating')->nullable();
            $table->unsignedSmallInteger('rapid_rating')->nullable();
            $table->unsignedSmallInteger('blitz_rating')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender_code', 20)->nullable();
            $table->string('club', 160)->nullable();
            $table->string('contact_information', 255)->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
