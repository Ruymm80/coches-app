<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['coche_id', 'buyer_id', 'seller_id', 'last_message_at'])]
class Chat extends Model
{
    protected $table = 'chats';

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function coche(): BelongsTo
    {
        return $this->belongsTo(Coche::class, 'coche_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'chat_id')->orderBy('created_at');
    }

    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(Mensaje::class, 'chat_id')->latestOfMany();
    }

    public function otroParticipante(User $user): User
    {
        return $user->is($this->buyer) ? $this->seller : $this->buyer;
    }

    public function unreadCountFor(User $user): int
    {
        return $this->mensajes()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
