<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Chat $chat): bool
    {
        return in_array($user->id, [$chat->buyer_id, $chat->seller_id], true);
    }

    public function reply(User $user, Chat $chat): bool
    {
        return $this->view($user, $chat);
    }
}
