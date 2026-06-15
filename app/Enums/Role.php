<?php

namespace App\Enums;

/** Roles posibles de un usuario: cliente normal o administrador. */
enum Role: string
{
    case User = 'user';
    case Admin = 'admin';

    /** Etiqueta legible para listar o mostrar el rol en la UI. */
    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuario',
            self::Admin => 'Administrador',
        };
    }
}
