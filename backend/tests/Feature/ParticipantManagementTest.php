<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ParticipantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_snapshots_can_be_registered_and_listed(): void
    {
        [$user, $tournament] = $this->createTournament();
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/tournaments/{$tournament->id}/participants", [
            'full_name' => 'Magnus Example',
            'fide_id' => '12345678',
            'federation_code' => 'NOR',
            'title' => 'GM',
            'standard_rating' => 2800,
        ])->assertCreated();

        $this->getJson("/api/v1/tournaments/{$tournament->id}/participants")
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.data.0.player.full_name', 'Magnus Example');
    }

    public function test_duplicate_fide_id_cannot_be_registered_twice(): void
    {
        [$user, $tournament] = $this->createTournament();
        Sanctum::actingAs($user);
        $payload = ['full_name' => 'Player One', 'fide_id' => '12345678'];

        $this->postJson("/api/v1/tournaments/{$tournament->id}/participants", $payload)->assertCreated();
        $this->postJson("/api/v1/tournaments/{$tournament->id}/participants", $payload)->assertStatus(422);
    }

    public function test_lock_assigns_tpn_by_rating_title_then_name(): void
    {
        [$user, $tournament] = $this->createTournament();
        Sanctum::actingAs($user);
        foreach ([
            ['full_name' => 'Zed', 'standard_rating' => 2000, 'title' => 'none'],
            ['full_name' => 'Alpha', 'standard_rating' => 2000, 'title' => 'GM'],
            ['full_name' => 'Beta', 'standard_rating' => 2100, 'title' => 'none'],
        ] as $player) {
            $this->postJson("/api/v1/tournaments/{$tournament->id}/participants", $player)->assertCreated();
        }

        $this->postJson("/api/v1/tournaments/{$tournament->id}/lock-participants")->assertOk();
        $this->getJson("/api/v1/tournaments/{$tournament->id}/participants")
            ->assertJsonPath('data.data.0.tpn', 1)
            ->assertJsonPath('data.data.0.player.full_name', 'Beta')
            ->assertJsonPath('data.data.1.player.full_name', 'Alpha');
    }

    private function createTournament(): array
    {
        $user = User::factory()->create();
        $tournament = \App\Models\Tournament::create([
            'slug' => fake()->unique()->slug(),
            'name' => 'Participant Test',
            'timezone' => 'UTC',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-01',
            'round_count' => 4,
            'status' => 'draft',
            'pairing_system' => 'fide_dutch',
            'ruleset_version' => 'fide-dutch-2026-02',
            'scoring_config' => ['win' => 2, 'draw' => 1, 'loss' => 0],
            'tie_break_config' => ['buchholz'],
            'initial_order_config' => ['policy' => 'fide_c04_2'],
            'regulations_snapshot' => [],
            'created_by' => $user->id,
        ]);
        $tournament->users()->attach($user->id, ['role' => 'chief_arbiter']);

        return [$user, $tournament];
    }
}
