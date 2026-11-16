<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_players', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('player_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('tpn')->nullable();
            $table->unsignedInteger('initial_rank')->nullable();
            $table->unsignedSmallInteger('rating_snapshot')->nullable();
            $table->string('rating_type', 20)->nullable();
            $table->date('rating_list_date')->nullable();
            $table->string('title_snapshot', 12)->default('none');
            $table->string('federation_snapshot', 3)->nullable();
            $table->string('club_snapshot', 160)->nullable();
            $table->string('contact_snapshot', 255)->nullable();
            $table->string('registration_status', 24)->default('registered');
            $table->string('pairing_status', 24)->default('active');
            $table->unsignedSmallInteger('late_entry_round')->nullable();
            $table->timestampTz('withdrawn_at')->nullable();
            $table->timestampsTz();
            $table->unique(['tournament_id', 'player_id']);
            $table->unique(['tournament_id', 'tpn']);
            $table->unique(['tournament_id', 'initial_rank']);
            $table->index(['tournament_id', 'registration_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_players');
    }
};
