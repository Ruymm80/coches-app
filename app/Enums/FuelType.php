<?php

namespace App\Enums;

enum FuelType: string
{
    case Gasoline = 'gasoline';
    case Diesel = 'diesel';
    case Hybrid = 'hybrid';
    case Electric = 'electric';
    case Lpg = 'lpg';
    case Cng = 'cng';

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

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->all();
    }
}
