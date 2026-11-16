<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParticipantRequest;
use App\Models\AuditLog;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TournamentPlayer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParticipantController extends Controller
{
    private const TITLE_ORDER = ['GM' => 1, 'IM' => 2, 'FM' => 3, 'CM' => 4, 'WGM' => 5, 'WIM' => 6, 'WFM' => 7, 'WCM' => 8, 'none' => 9];

    public function index(Request $request, Tournament $tournament): JsonResponse
    {
        Gate::authorize('view', $tournament);
        $query = $tournament->participants()->with('player')->orderBy('tpn')->orderBy('id');
        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->whereHas('player', fn ($players) => $players->where('full_name', 'like', "%{$search}%")->orWhere('fide_id', $search));
        }
        if ($request->filled('registration_status')) {
            $query->where('registration_status', $request->string('registration_status'));
        }

        return response()->json(['data' => $query->paginate(50), 'meta' => [
            'total' => $tournament->participants()->count(),
            'active' => $tournament->participants()->where('pairing_status', 'active')->count(),
            'locked' => $tournament->status !== 'draft' && $tournament->status !== 'registration',
        ]]);
    }

    public function store(StoreParticipantRequest $request, Tournament $tournament): JsonResponse
    {
        Gate::authorize('manage', $tournament);
        abort_if(in_array($tournament->status, ['in_progress', 'completed', 'locked'], true), 409, 'The participant list is locked.');
        $data = $request->validated();
        if (! empty($data['fide_id']) && $tournament->participants()->whereHas('player', fn ($players) => $players->where('fide_id', $data['fide_id']))->exists()) {
            return response()->json(['error' => [
                'code' => 'DUPLICATE_PARTICIPANT',
                'message' => 'This FIDE ID is already registered in the tournament.',
            ]], 422);
        }
        $participant = DB::transaction(function () use ($data, $tournament, $request): TournamentPlayer {
            $player = ! empty($data['fide_id'])
                ? Player::firstOrCreate(['fide_id' => $data['fide_id']], ['full_name' => $data['full_name']])
                : Player::create(['full_name' => $data['full_name']]);
            if ($player->exists && $player->fide_id && $player->full_name !== $data['full_name']) {
                $player->update(['full_name' => $data['full_name']]);
            }
            $player->update(collect($data)->except('full_name')->all());
            $participant = $tournament->participants()->create([
                'player_id' => $player->id,
                'rating_snapshot' => $data['standard_rating'] ?? null,
                'rating_type' => 'standard',
                'title_snapshot' => $data['title'] ?? 'none',
                'federation_snapshot' => $data['federation_code'] ?? null,
                'club_snapshot' => $data['club'] ?? null,
                'contact_snapshot' => $data['contact_information'] ?? null,
            ]);
            $this->audit($request, $tournament, 'participant.created', $participant);
            return $participant->load('player');
        });

        return response()->json(['data' => $participant], 201);
    }

    public function update(StoreParticipantRequest $request, Tournament $tournament, TournamentPlayer $participant): JsonResponse
    {
        Gate::authorize('manage', $tournament);
        abort_unless($participant->tournament_id === $tournament->id, 404);
        abort_if($tournament->status === 'in_progress' || $participant->tpn !== null, 409, 'Participant snapshots are locked.');
        $data = $request->validated();
        $participant->player->update(collect($data)->except('full_name')->all() + ['full_name' => $data['full_name']]);
        $participant->update([
            'rating_snapshot' => $data['standard_rating'] ?? null,
            'title_snapshot' => $data['title'] ?? 'none',
            'federation_snapshot' => $data['federation_code'] ?? null,
            'club_snapshot' => $data['club'] ?? null,
            'contact_snapshot' => $data['contact_information'] ?? null,
        ]);

        return response()->json(['data' => $participant->load('player')]);
    }

    public function destroy(Request $request, Tournament $tournament, TournamentPlayer $participant): JsonResponse
    {
        Gate::authorize('manage', $tournament);
        abort_unless($participant->tournament_id === $tournament->id, 404);
        abort_if($tournament->status === 'in_progress' || $participant->tpn !== null, 409, 'Participant list is locked.');
        $participant->delete();

        return response()->json(status: 204);
    }

    public function lock(Request $request, Tournament $tournament): JsonResponse
    {
        Gate::authorize('manage', $tournament);
        abort_if($tournament->status === 'in_progress', 409, 'Tournament has already started.');
        $participants = $tournament->participants()->with('player')->where('registration_status', '!=', 'withdrawn')->get();
        abort_if($participants->isEmpty(), 422, 'At least one participant is required.');
        DB::transaction(function () use ($participants, $tournament, $request): void {
            $ordered = $participants->sort(function (TournamentPlayer $a, TournamentPlayer $b): int {
                $ratingComparison = ($b->rating_snapshot ?? -1) <=> ($a->rating_snapshot ?? -1);
                if ($ratingComparison !== 0) {
                    return $ratingComparison;
                }
                $titleComparison = (self::TITLE_ORDER[$a->title_snapshot] ?? 9) <=> (self::TITLE_ORDER[$b->title_snapshot] ?? 9);
                if ($titleComparison !== 0) {
                    return $titleComparison;
                }
                return strcasecmp($a->player->full_name, $b->player->full_name);
            })->values();
            foreach ($ordered as $index => $participant) {
                $participant->update(['tpn' => $index + 1, 'initial_rank' => $index + 1, 'registration_status' => 'confirmed']);
            }
            $tournament->update(['status' => 'registration', 'version' => $tournament->version + 1]);
            $this->audit($request, $tournament, 'participant-list.locked', $tournament);
        });

        return response()->json(['data' => $tournament->fresh()->load('participants.player')]);
    }

    public function import(Request $request, Tournament $tournament): JsonResponse
    {
        Gate::authorize('manage', $tournament);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'], 'confirm' => ['sometimes', 'boolean']]);
        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $rows = array_values(array_filter($spreadsheet->getActiveSheet()->toArray(), fn (array $row) => count(array_filter($row)) > 0));
        abort_if(count($rows) < 2, 422, 'The import must include a header and at least one participant.');
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), array_shift($rows));
        $preview = collect($rows)->map(fn (array $row) => array_combine($headers, array_pad($row, count($headers), null)))->values();
        if (! $request->boolean('confirm')) {
            return response()->json(['data' => $preview, 'meta' => ['count' => $preview->count(), 'preview' => true]]);
        }
        $created = 0;
        foreach ($preview as $row) {
            $this->createFromImportRow($row, $tournament, $request);
            $created++;
        }

        return response()->json(['data' => ['imported' => $created]], 201);
    }

    private function createFromImportRow(array $row, Tournament $tournament, Request $request): void
    {
        $data = [
            'full_name' => $row['full_name'] ?? $row['name'] ?? null,
            'fide_id' => $row['fide_id'] ?? null,
            'federation_code' => $row['federation'] ?? $row['federation_code'] ?? null,
            'title' => $row['title'] ?: 'none',
            'standard_rating' => $row['standard_rating'] ?? $row['rating'] ?? null,
            'rapid_rating' => $row['rapid_rating'] ?? null,
            'blitz_rating' => $row['blitz_rating'] ?? null,
            'club' => $row['club'] ?? null,
        ];
        validator($data, (new StoreParticipantRequest)->rules())->validate();
        $this->store(new StoreParticipantRequest($data), $tournament);
    }

    private function audit(Request $request, Tournament $tournament, string $action, object $entity): void
    {
        AuditLog::create([
            'tournament_id' => $tournament->id, 'actor_user_id' => $request->user()->id,
            'action' => $action, 'entity_type' => $entity::class, 'entity_id' => $entity->id,
            'after_state' => $entity->toArray(), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(),
        ]);
    }
}
