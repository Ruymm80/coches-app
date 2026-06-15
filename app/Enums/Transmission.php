<?php

namespace App\Enums;

/** Tipo de caja de cambios del vehículo. */
enum Transmission: string
{
    case Manual = 'manual';
    case Automatic = 'automatic';

    /** Etiqueta legible para mostrar en formularios y vistas. */
    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Automatic => 'Automático',
        };
    }

    /** Devuelve un array valor → etiqueta listo para usar en un select. */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->all();
    }
}
