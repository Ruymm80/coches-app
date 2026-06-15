<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

/**
 * Reglas de autorización sobre las conversaciones. Un chat solo es accesible
 * por sus dos participantes (comprador y vendedor) o por un administrador.
 */
class ChatPolicy
{
    /** Los administradores pueden acceder a cualquier conversación. */
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /** El usuario solo puede ver el chat si participa en él. */
    public function view(User $user, Chat $chat): bool
    {
        return in_array($user->id, [$chat->buyer_id, $chat->seller_id], true);
    }

    /** Quien puede ver un chat también puede responder en él. */
    public function reply(User $user, Chat $chat): bool
    {
        return $this->view($user, $chat);
    }
}
