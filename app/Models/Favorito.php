<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabla pivote N:M entre usuarios y coches que representa la lista de
 * favoritos de cada usuario. Se modela como modelo propio para permitir
 * timestamps y consultas directas sobre la tabla.
 */
#[Fillable(['user_id', 'coche_id'])]
class Favorito extends Model
{
    protected $table = 'favoritos';

    /** Usuario que ha guardado el coche en favoritos. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Coche marcado como favorito. */
    public function coche(): BelongsTo
    {
        return $this->belongsTo(Coche::class, 'coche_id');
    }
}
