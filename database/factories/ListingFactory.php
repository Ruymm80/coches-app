<?php

namespace Database\Factories;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\ListingStatus;
use App\Enums\Transmission;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    protected static array $catalog = [
        'Audi' => ['A1', 'A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7'],
        'BMW' => ['Serie 1', 'Serie 3', 'Serie 5', 'X1', 'X3', 'X5'],
        'Mercedes-Benz' => ['Clase A', 'Clase C', 'Clase E', 'GLA', 'GLC'],
        'Volkswagen' => ['Golf', 'Polo', 'Passat', 'Tiguan', 'T-Roc'],
        'SEAT' => ['Ibiza', 'León', 'Ateca', 'Arona', 'Tarraco'],
        'Renault' => ['Clio', 'Mégane', 'Captur', 'Kadjar', 'Arkana'],
        'Peugeot' => ['208', '308', '3008', '5008', '2008'],
        'Citroën' => ['C3', 'C4', 'C5 Aircross', 'Berlingo'],
        'Ford' => ['Fiesta', 'Focus', 'Kuga', 'Puma', 'Mondeo'],
        'Toyota' => ['Yaris', 'Corolla', 'C-HR', 'RAV4', 'Auris'],
        'Hyundai' => ['i20', 'i30', 'Tucson', 'Kona', 'IONIQ'],
        'Kia' => ['Picanto', 'Rio', 'Ceed', 'Sportage', 'Niro'],
    ];

    protected static array $colors = [
        'Blanco', 'Negro', 'Gris', 'Plata', 'Azul', 'Rojo',
        'Verde', 'Amarillo', 'Marrón', 'Beige',
    ];

    protected static array $provinces = [
        'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza', 'Málaga',
        'Murcia', 'Palma', 'Las Palmas', 'Bilbao', 'Alicante', 'Córdoba',
        'Valladolid', 'Vigo', 'Gijón', 'A Coruña', 'Granada', 'Vitoria',
    ];

    public function definition(): array
    {
        $brand = fake()->randomElement(array_keys(self::$catalog));
        $model = fake()->randomElement(self::$catalog[$brand]);
        $year = fake()->numberBetween(2005, 2025);
        $title = "{$brand} {$model} {$year}";

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'brand' => $brand,
            'model' => $model,
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->numberBetween(2000, 60000),
            'year' => $year,
            'mileage_km' => fake()->numberBetween(0, 280000),
            'fuel_type' => fake()->randomElement(FuelType::cases())->value,
            'transmission' => fake()->randomElement(Transmission::cases())->value,
            'body_type' => fake()->randomElement(BodyType::cases())->value,
            'color' => fake()->randomElement(self::$colors),
            'province' => fake()->randomElement(self::$provinces),
            'status' => ListingStatus::Active->value,
            'featured' => fake()->boolean(15),
            'views_count' => fake()->numberBetween(0, 500),
        ];
    }

    public function sold(): static
    {
        return $this->state(fn () => ['status' => ListingStatus::Sold->value]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ListingStatus::Draft->value]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
