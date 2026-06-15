<?php

namespace App\Enums;

/** Estados posibles del ciclo de vida de un anuncio. */
enum ListingStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Sold = 'sold';
    case Expired = 'expired';

    /** Etiqueta legible en español para mostrar en las vistas. */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Active => 'Activo',
            self::Sold => 'Vendido',
            self::Expired => 'Expirado',
        };
    }

    /** Clases CSS de Tailwind para pintar la insignia de estado en color. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-700',
            self::Active => 'bg-green-100 text-green-700',
            self::Sold => 'bg-blue-100 text-blue-700',
            self::Expired => 'bg-red-100 text-red-700',
        };
    }
}
