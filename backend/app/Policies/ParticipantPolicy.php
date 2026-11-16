<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class ParticipantPolicy
{
    public function manage(User $user, Tournament $tournament): bool
    {
        return $user->is_platform_admin || $tournament->users()
            ->whereKey($user->id)
            ->wherePivotIn('role', ['chief_arbiter', 'organizer', 'arbiter'])
            ->exists();
    }
}
