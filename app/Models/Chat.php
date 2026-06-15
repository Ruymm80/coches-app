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

    /** Coche al que pertenece la conversación. */
    public function coche(): BelongsTo
    {
        return $this->belongsTo(Coche::class, 'coche_id');
    }

    /** Usuario interesado (comprador potencial) que inició el chat. */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /** Usuario dueño del anuncio (vendedor). */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** Todos los mensajes del chat en orden cronológico. */
    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'chat_id')->orderBy('created_at');
    }

    /** Último mensaje del chat, útil para listados sin cargar todo el historial. */
    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(Mensaje::class, 'chat_id')->latestOfMany();
    }

    /** Devuelve el otro participante del chat respecto al usuario dado. */
    public function otroParticipante(User $user): User
    {
        return $user->is($this->buyer) ? $this->seller : $this->buyer;
    }

    /** Número de mensajes pendientes de leer para el usuario indicado. */
    public function unreadCountFor(User $user): int
    {
        return $this->mensajes()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
