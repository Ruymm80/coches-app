<?php

namespace Database\Factories;

use App\Models\Coche;
use App\Models\Imagen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Imagen>
 */
class ImagenFactory extends Factory
{
    public function definition(): array
    {
        $seed = fake()->numberBetween(1, 5000);

        return [
            'coche_id' => Coche::factory(),
            'path' => "https://loremflickr.com/800/600/car?lock={$seed}",
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'sort_order' => 0]);
    }
}
