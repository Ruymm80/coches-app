<?php

namespace App\Models;

use Database\Factories\MensajeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['chat_id', 'sender_id', 'body', 'read_at'])]
class Mensaje extends Model
{
    /** @use HasFactory<MensajeFactory> */
    use HasFactory;

    protected $table = 'mensajes';

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Al crear un mensaje se actualiza la marca de "último mensaje" del chat,
        // para que el listado de conversaciones se pueda ordenar por actividad.
        static::created(function (Mensaje $mensaje) {
            $mensaje->chat()->update([
                'last_message_at' => $mensaje->created_at,
            ]);
        });
    }

    /** Conversación a la que pertenece el mensaje. */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    /** Usuario que envió el mensaje. */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
