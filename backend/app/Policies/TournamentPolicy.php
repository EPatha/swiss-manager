<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class TournamentPolicy
{
    public function view(User $user, Tournament $tournament): bool
    {
        return $user->is_platform_admin || $tournament->users()->whereKey($user->id)->exists();
    }

    public function update(User $user, Tournament $tournament): bool
    {
        return $user->is_platform_admin || $this->hasRole($user, $tournament, ['chief_arbiter', 'organizer']);
    }

    private function hasRole(User $user, Tournament $tournament, array $roles): bool
    {
        return $tournament->users()
            ->whereKey($user->id)
            ->wherePivotIn('role', $roles)
            ->exists();
    }
}
