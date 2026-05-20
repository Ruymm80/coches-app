<?php

namespace Database\Factories;

use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingImage>
 */
class ListingImageFactory extends Factory
{
    public function definition(): array
    {
        $seed = fake()->numberBetween(1, 5000);

        return [
            'listing_id' => Listing::factory(),
            'path' => "https://loremflickr.com/800/600/car,automobile/all?lock={$seed}",
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(fn () => ['is_primary' => true, 'sort_order' => 0]);
    }
}
