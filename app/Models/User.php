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

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'province', 'avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function coches(): HasMany
    {
        return $this->hasMany(Coche::class);
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function favoritedCoches()
    {
        return $this->belongsToMany(Coche::class, 'favoritos', 'user_id', 'coche_id')->withTimestamps();
    }

    public function chatsAsBuyer(): HasMany
    {
        return $this->hasMany(Chat::class, 'buyer_id');
    }

    public function chatsAsSeller(): HasMany
    {
        return $this->hasMany(Chat::class, 'seller_id');
    }

    public function sentMensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'sender_id');
    }

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
