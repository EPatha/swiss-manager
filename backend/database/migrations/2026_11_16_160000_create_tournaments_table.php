<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug', 180)->unique();
            $table->string('name', 200);
            $table->string('venue', 255)->nullable();
            $table->string('timezone', 64);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('round_count');
            $table->string('status', 24)->default('draft');
            $table->string('pairing_system', 40)->default('fide_dutch');
            $table->string('ruleset_version', 80);
            $table->jsonb('scoring_config');
            $table->jsonb('tie_break_config');
            $table->jsonb('initial_order_config');
            $table->jsonb('regulations_snapshot');
            $table->boolean('is_public')->default(false);
            $table->unsignedBigInteger('version')->default(1);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        Schema::create('tournament_user', function (Blueprint $table): void {
            $table->foreignUuid('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32);
            $table->timestampsTz();
            $table->primary(['tournament_id', 'user_id']);
            $table->unique(['tournament_id', 'user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_user');
        Schema::dropIfExists('tournaments');
    }
};
