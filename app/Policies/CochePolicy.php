<?php

namespace App\Policies;

use App\Models\Coche;
use App\Models\User;

/**
 * Reglas de autorización sobre los anuncios. Los administradores tienen
 * acceso completo gracias al hook before(); el resto de usuarios solo
 * pueden gestionar sus propios anuncios.
 */
class CochePolicy
{
    /** Los administradores pueden hacer cualquier cosa sin pasar por el resto de reglas. */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** Cualquiera ve los anuncios activos; los borradores solo los ve su dueño. */
    public function view(?User $user, Coche $coche): bool
    {
        if ($coche->status->value === 'active') {
            return true;
        }

        return $user && $user->id === $coche->user_id;
    }

    /** Cualquier usuario autenticado puede crear anuncios. */
    public function create(User $user): bool
    {
        return true;
    }

    /** Solo el propietario puede editar su anuncio. */
    public function update(User $user, Coche $coche): bool
    {
        return $user->id === $coche->user_id;
    }

    /** Solo el propietario puede borrar su anuncio. */
    public function delete(User $user, Coche $coche): bool
    {
        return $user->id === $coche->user_id;
    }
}
