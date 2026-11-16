<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTournamentRequest;
use App\Models\AuditLog;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TournamentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Tournament::query();

        if (! $user->is_platform_admin) {
            $query->whereHas('users', fn ($roles) => $roles->whereKey($user->id));
        }

        return response()->json([
            'data' => $query->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function store(StoreTournamentRequest $request): JsonResponse
    {
        $tournament = DB::transaction(function () use ($request): Tournament {
            $tournament = Tournament::create([
                ...$request->validated(),
                'initial_order_config' => ['policy' => 'fide_c04_2'],
                'regulations_snapshot' => [],
                'created_by' => $request->user()->id,
            ]);
            $tournament->users()->attach($request->user()->id, ['role' => 'chief_arbiter']);
            AuditLog::create([
                'tournament_id' => $tournament->id,
                'actor_user_id' => $request->user()->id,
                'action' => 'tournament.created',
                'entity_type' => Tournament::class,
                'entity_id' => $tournament->id,
                'after_state' => $tournament->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $tournament;
        });

        return response()->json(['data' => $tournament], 201);
    }

    public function show(Tournament $tournament): JsonResponse
    {
        Gate::authorize('view', $tournament);

        return response()->json(['data' => $tournament]);
    }

    public function publicShow(string $slug): JsonResponse
    {
        $tournament = Tournament::where('slug', $slug)->where('is_public', true)->firstOrFail();

        return response()->json(['data' => $tournament->only([
            'id', 'slug', 'name', 'timezone', 'start_date', 'end_date', 'round_count',
            'status', 'pairing_system', 'ruleset_version',
        ])]);
    }
}
