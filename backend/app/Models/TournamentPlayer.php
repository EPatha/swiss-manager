<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentPlayer extends Model
{
    use HasUuids;

    protected $fillable = [
        'tournament_id', 'player_id', 'tpn', 'initial_rank', 'rating_snapshot',
        'rating_type', 'rating_list_date', 'title_snapshot', 'federation_snapshot',
        'club_snapshot', 'contact_snapshot', 'registration_status', 'pairing_status',
        'late_entry_round', 'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'rating_list_date' => 'date',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
