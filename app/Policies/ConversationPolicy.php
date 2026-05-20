<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return in_array($user->id, [$conversation->buyer_id, $conversation->seller_id], true);
    }

    public function reply(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }
}
