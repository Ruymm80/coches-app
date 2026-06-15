<?php

namespace App\Enums;

/** Tipo de combustible que utiliza el vehículo. */
enum FuelType: string
{
    case Gasoline = 'gasoline';
    case Diesel = 'diesel';
    case Hybrid = 'hybrid';
    case Electric = 'electric';
    case Lpg = 'lpg';
    case Cng = 'cng';

    /** Etiqueta legible en español para usar en formularios y filtros. */
    public function label(): string
    {
        return match ($this) {
            self::Gasoline => 'Gasolina',
            self::Diesel => 'Diésel',
            self::Hybrid => 'Híbrido',
            self::Electric => 'Eléctrico',
            self::Lpg => 'GLP',
            self::Cng => 'GNC',
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
