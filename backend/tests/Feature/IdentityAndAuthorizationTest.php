<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdentityAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_register_and_login_with_a_bearer_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Chief Arbiter',
            'email' => 'arbiter@example.test',
            'password' => 'correct horse battery staple',
            'password_confirmation' => 'correct horse battery staple',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'arbiter@example.test',
            'password' => 'correct horse battery staple',
        ])->assertOk();

        $this->withToken($response->json('data.token'))
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'arbiter@example.test');
    }

    public function test_tournament_access_is_scoped_to_assigned_users(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($owner);
        $created = $this->postJson('/api/v1/tournaments', [
            'name' => 'Scoped Open',
            'slug' => 'scoped-open',
            'timezone' => 'UTC',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
            'round_count' => 5,
            'pairing_system' => 'fide_dutch',
            'ruleset_version' => 'fide-dutch-2026-02',
            'scoring_config' => ['win' => 2, 'draw' => 1, 'loss' => 0],
            'tie_break_config' => ['buchholz'],
        ])->assertCreated();

        Sanctum::actingAs($otherUser);
        $this->getJson('/api/v1/tournaments/'.$created->json('data.id'))->assertForbidden();
    }

    public function test_unauthenticated_users_cannot_access_private_tournaments(): void
    {
        $this->getJson('/api/v1/tournaments')->assertUnauthorized();
    }
}
