<?php

namespace App\Models;

use Database\Factories\ImagenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['coche_id', 'path', 'sort_order', 'is_primary'])]
class Imagen extends Model
{
    /** @use HasFactory<ImagenFactory> */
    use HasFactory;

    protected $table = 'imagenes';

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function coche(): BelongsTo
    {
        return $this->belongsTo(Coche::class, 'coche_id');
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return Storage::disk('public')->url($this->path);
    }
}
