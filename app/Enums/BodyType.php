<?php

namespace App\Enums;

enum BodyType: string
{
    case Sedan = 'sedan';
    case Suv = 'suv';
    case Hatchback = 'hatchback';
    case StationWagon = 'station_wagon';
    case Coupe = 'coupe';
    case Convertible = 'convertible';
    case Pickup = 'pickup';
    case Van = 'van';

    public function label(): string
    {
        return match ($this) {
            self::Sedan => 'Berlina',
            self::Suv => 'SUV / 4x4',
            self::Hatchback => 'Utilitario',
            self::StationWagon => 'Familiar',
            self::Coupe => 'Coupé',
            self::Convertible => 'Cabrio',
            self::Pickup => 'Pick-up',
            self::Van => 'Furgoneta',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->all();
    }
}
