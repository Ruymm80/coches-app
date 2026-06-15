<?php

namespace App\Models;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\ListingStatus;
use App\Enums\Transmission;
use Database\Factories\CocheFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'user_id', 'title', 'slug', 'brand', 'model', 'description',
    'price', 'year', 'mileage_km', 'fuel_type', 'transmission',
    'body_type', 'color', 'province', 'status', 'featured',
])]
class Coche extends Model
{
    /** @use HasFactory<CocheFactory> */
    use HasFactory;

    protected $table = 'coches';

    protected function casts(): array
    {
        return [
            'fuel_type' => FuelType::class,
            'transmission' => Transmission::class,
            'body_type' => BodyType::class,
            'status' => ListingStatus::class,
            'featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Genera un slug único al crear y lo regenera si cambia el título.
        static::creating(function (Coche $coche) {
            if (blank($coche->slug)) {
                $coche->slug = static::generateUniqueSlug($coche->title);
            }
        });

        static::updating(function (Coche $coche) {
            if ($coche->isDirty('title') && ! $coche->isDirty('slug')) {
                $coche->slug = static::generateUniqueSlug($coche->title, $coche->id);
            }
        });
    }

    /** Genera un slug único a partir del título, añadiendo sufijo numérico si ya existe. */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /** Usa el slug como clave de ruta en lugar del id (URLs amigables). */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Usuario propietario / vendedor del anuncio. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Galería de imágenes del anuncio, ordenadas por posición. */
    public function imagenes(): HasMany
    {
        return $this->hasMany(Imagen::class, 'coche_id')->orderBy('sort_order');
    }

    /** Imagen principal que se usa como portada del anuncio. */
    public function imagenPrincipal(): HasOne
    {
        return $this->hasOne(Imagen::class, 'coche_id')->where('is_primary', true);
    }

    /** Registros de favoritos asociados a este coche. */
    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class, 'coche_id');
    }

    /** Usuarios que han marcado este coche como favorito. */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favoritos', 'coche_id', 'user_id')->withTimestamps();
    }

    /** Chats abiertos sobre este anuncio. */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'coche_id');
    }

    /** Filtra solo los anuncios en estado Activo. */
    public function scopeActive($query)
    {
        return $query->where('status', ListingStatus::Active);
    }

    /** Comprueba si el coche está marcado como favorito por el usuario dado. */
    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favoritos()->where('user_id', $user->id)->exists();
    }
}
