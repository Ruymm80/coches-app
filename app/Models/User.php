<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'province', 'avatar', 'avatar_mime', 'avatar_data'])]
#[Hidden(['password', 'remember_token', 'avatar_data'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static array $defaultColumns = [
        'id', 'name', 'email', 'email_verified_at', 'password', 'role',
        'phone', 'province', 'avatar', 'avatar_mime', 'remember_token',
        'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    protected static function booted(): void
    {
        // No cargar avatar_data (BLOB) por defecto para evitar query pesado.
        static::addGlobalScope('exclude_avatar_data', function ($builder) {
            if (empty($builder->getQuery()->columns)) {
                $builder->select(array_map(fn ($c) => 'users.'.$c, self::$defaultColumns));
            }
        });
    }

    public function scopeWithAvatarData($query)
    {
        return $query->withoutGlobalScope('exclude_avatar_data');
    }

    /** URL pública de la foto de perfil, o cadena vacía si no hay avatar. */
    public function avatarUrl(): string
    {
        if (! is_null($this->avatar_mime)) {
            return route('avatar.show', $this);
        }

        return '';
    }

    /** Inicial del nombre para mostrar como avatar por defecto. */
    public function avatarInitial(): string
    {
        return strtoupper(mb_substr($this->name, 0, 1));
    }

    /** Indica si el usuario es administrador. */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /** Anuncios publicados por el usuario. */
    public function coches(): HasMany
    {
        return $this->hasMany(Coche::class);
    }

    /** Registros directos de favoritos (tabla pivote). */
    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    /** Coches favoritos del usuario, accesibles directamente como colección de Coche. */
    public function favoritedCoches()
    {
        return $this->belongsToMany(Coche::class, 'favoritos', 'user_id', 'coche_id')->withTimestamps();
    }

    /** Chats en los que el usuario participa como comprador. */
    public function chatsAsBuyer(): HasMany
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    /** Chats en los que el usuario participa como vendedor. */
    public function chatsAsSeller(): HasMany
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    /** Mensajes enviados por el usuario. */
    public function sentMensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'sender_id');
    }

    /** Número total de mensajes sin leer del usuario (en cualquiera de sus chats). */
    public function unreadMessagesCount(): int
    {
        return Mensaje::query()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $this->id)
            ->whereIn('chat_id', function ($q) {
                $q->select('id')
                    ->from('chats')
                    ->where('buyer_id', $this->id)
                    ->orWhere('seller_id', $this->id);
            })
            ->count();
    }
}
