<?php

namespace App\Support;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\Transmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Construye dinámicamente la consulta del listado público de coches a
 * partir de los parámetros de la URL (?brand=, ?price_min=, ?sort=, ...).
 * Mantiene el controlador limpio centralizando todos los filtros aquí.
 */
class CocheFilter
{
    /** Modos de ordenación soportados por el listado, con su etiqueta legible. */
    public const SORTS = [
        'recent' => 'Más recientes',
        'price_asc' => 'Precio: menor a mayor',
        'price_desc' => 'Precio: mayor a menor',
        'year_desc' => 'Año: más nuevos',
        'km_asc' => 'Kilómetros: menos km',
    ];

    public function __construct(protected Request $request) {}

    /** Aplica los filtros y la ordenación al query builder y lo devuelve. */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->str('q'), fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('title', 'like', "%{$v}%")
                    ->orWhere('brand', 'like', "%{$v}%")
                    ->orWhere('model', 'like', "%{$v}%");
            }))
            ->when($this->str('brand'), fn ($q, $v) => $q->where('brand', 'like', "%{$v}%"))
            ->when($this->str('model'), fn ($q, $v) => $q->where('model', 'like', "%{$v}%"))
            ->when($this->int('price_min'), fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($this->int('price_max'), fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($this->int('year_min'), fn ($q, $v) => $q->where('year', '>=', $v))
            ->when($this->int('year_max'), fn ($q, $v) => $q->where('year', '<=', $v))
            ->when($this->int('km_max'), fn ($q, $v) => $q->where('mileage_km', '<=', $v))
            ->when($this->enum('fuel', FuelType::class), fn ($q, $v) => $q->where('fuel_type', $v->value))
            ->when($this->enum('transmission', Transmission::class), fn ($q, $v) => $q->where('transmission', $v->value))
            ->when($this->enum('body', BodyType::class), fn ($q, $v) => $q->where('body_type', $v->value))
            ->when($this->str('province'), fn ($q, $v) => $q->where('province', $v))
            ->tap(fn ($q) => $this->applySort($q));
    }

    /** Añade la cláusula ORDER BY al query según el parámetro ?sort=. */
    protected function applySort(Builder $query): void
    {
        match ($this->str('sort') ?? 'recent') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'year_desc' => $query->orderByDesc('year'),
            'km_asc' => $query->orderBy('mileage_km'),
            default => $query->orderByDesc('featured')->orderByDesc('created_at'),
        };
    }

    /** Lee un parámetro string del query, devolviendo null si está vacío. */
    protected function str(string $key): ?string
    {
        $val = $this->request->query($key);

        return is_string($val) && $val !== '' ? trim($val) : null;
    }

    /** Lee un parámetro entero del query, devolviendo null si no es numérico. */
    protected function int(string $key): ?int
    {
        $val = $this->request->query($key);

        return is_numeric($val) ? (int) $val : null;
    }

    /** Lee un parámetro y lo convierte al enum indicado, o null si no coincide. */
    protected function enum(string $key, string $enumClass): ?object
    {
        $val = $this->str($key);

        return $val ? $enumClass::tryFrom($val) : null;
    }
}
