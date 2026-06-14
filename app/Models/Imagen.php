<?php

namespace App\Models;

use Database\Factories\ImagenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['coche_id', 'path', 'mime_type', 'data', 'sort_order', 'is_primary'])]
class Imagen extends Model
{
    /** @use HasFactory<ImagenFactory> */
    use HasFactory;

    protected $table = 'imagenes';

    /**
     * Columnas que NO se cargan por defecto (el BLOB es pesado).
     * Para obtener el binario hay que usar Imagen::withData()->find(...).
     */
    protected static array $defaultColumns = [
        'id', 'coche_id', 'path', 'mime_type', 'sort_order', 'is_primary', 'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // No cargar la columna 'data' (BLOB) por defecto.
        // Importante: NO pisar las columnas si la consulta ya tiene un select propio
        // (p.ej. withCount o subqueries de agregación), porque rompería su count(*).
        static::addGlobalScope('exclude_data', function ($builder) {
            if (empty($builder->getQuery()->columns)) {
                $builder->select(array_map(fn ($c) => 'imagenes.'.$c, self::$defaultColumns));
            }
        });
    }

    /** Scope para cargar también el binario */
    public function scopeWithData($query)
    {
        return $query->withoutGlobalScope('exclude_data');
    }

    public function coche(): BelongsTo
    {
        return $this->belongsTo(Coche::class, 'coche_id');
    }

    /**
     * URL pública de la imagen. Orden de preferencia:
     *  1. URL externa (path comienza por http) → devuelve tal cual.
     *  2. Si hay binario en BD (mime_type seteado) → endpoint /imagenes/{id}.
     *  3. Path local en storage → URL del symlink.
     */
    public function getUrlAttribute(): string
    {
        if ($this->path && (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://'))) {
            return $this->path;
        }

        if (! is_null($this->mime_type)) {
            return route('imagenes.show', $this);
        }

        if ($this->path) {
            return Storage::disk('public')->url($this->path);
        }

        return '';
    }
}
