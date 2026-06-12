<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'coche_id'])]
class Favorito extends Model
{
    protected $table = 'favoritos';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coche(): BelongsTo
    {
        return $this->belongsTo(Coche::class, 'coche_id');
    }
}
