<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasUuids;

    protected $fillable = [
        'full_name', 'fide_id', 'federation_code', 'title', 'standard_rating',
        'rapid_rating', 'blitz_rating', 'birth_date', 'gender_code', 'club',
        'contact_information',
    ];

    public function tournamentParticipants(): HasMany
    {
        return $this->hasMany(TournamentPlayer::class);
    }
}
