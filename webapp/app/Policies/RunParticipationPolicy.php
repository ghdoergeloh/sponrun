<?php

namespace App\Policies;

use App\Models\RunParticipation;
use App\Models\User;

class RunParticipationPolicy
{
    public function view(User $user, RunParticipation $runpart): bool
    {
        return $user->id === $runpart->user_id || $user->hasRole('admin');
    }

    public function update(User $user, RunParticipation $runpart): bool
    {
        return $user->id === $runpart->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, RunParticipation $runpart): bool
    {
        return $user->id === $runpart->user_id || $user->hasRole('admin');
    }
}
