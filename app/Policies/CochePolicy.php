<?php

namespace App\Policies;

use App\Models\Coche;
use App\Models\User;

class CochePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(?User $user, Coche $coche): bool
    {
        if ($coche->status->value === 'active') {
            return true;
        }

        return $user && $user->id === $coche->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Coche $coche): bool
    {
        return $user->id === $coche->user_id;
    }

    public function delete(User $user, Coche $coche): bool
    {
        return $user->id === $coche->user_id;
    }
}
