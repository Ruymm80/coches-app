<?php

namespace Tests\Feature;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\ListingStatus;
use App\Enums\Role;
use App\Enums\Transmission;
use App\Models\Coche;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAreaSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)
            ->get(route('perfil.dashboard'))
            ->assertOk()
            ->assertSee('Hola, '.$user->name);
    }

    public function test_user_can_create_a_listing(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $payload = [
            'title' => 'BMW Serie 3 2020',
            'brand' => 'BMW',
            'model' => 'Serie 3',
            'description' => 'Coche en buen estado con todas las revisiones al día.',
            'price' => 18500,
            'year' => 2020,
            'mileage_km' => 45000,
            'fuel_type' => FuelType::Diesel->value,
            'transmission' => Transmission::Automatic->value,
            'body_type' => BodyType::Sedan->value,
            'color' => 'Negro',
            'province' => 'Madrid',
            'status' => ListingStatus::Active->value,
            'images' => [UploadedFile::fake()->image('foto.jpg', 800, 600)],
        ];

        $this->actingAs($user)
            ->post(route('coches.store'), $payload)
            ->assertRedirect(route('coches.mine'));

        $this->assertDatabaseHas('coches', [
            'user_id' => $user->id,
            'title' => 'BMW Serie 3 2020',
            'status' => ListingStatus::Active->value,
        ]);

        $coche = Coche::firstWhere('title', 'BMW Serie 3 2020');
        $this->assertCount(1, $coche->imagenes);
        Storage::disk('public')->assertExists($coche->imagenes->first()->path);
    }

    public function test_user_cannot_edit_others_listing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $coche = Coche::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('coches.edit', $coche))
            ->assertForbidden();
    }

    public function test_admin_can_edit_any_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $coche = Coche::factory()->create();

        $this->actingAs($admin)
            ->get(route('coches.edit', $coche))
            ->assertOk();
    }

    public function test_owner_can_mark_listing_as_sold(): void
    {
        $owner = User::factory()->create();
        $coche = Coche::factory()->for($owner)->create([
            'status' => ListingStatus::Active->value,
        ]);

        $this->actingAs($owner)
            ->patch(route('coches.mark-sold', $coche))
            ->assertRedirect();

        $this->assertSame(ListingStatus::Sold, $coche->fresh()->status);
    }

    public function test_favorite_toggle(): void
    {
        $user = User::factory()->create();
        $coche = Coche::factory()->create();

        $this->actingAs($user)
            ->post(route('coches.favorite', $coche))
            ->assertRedirect();

        $this->assertDatabaseHas('favoritos', [
            'user_id' => $user->id,
            'coche_id' => $coche->id,
        ]);

        $this->actingAs($user)
            ->post(route('coches.favorite', $coche))
            ->assertRedirect();

        $this->assertDatabaseMissing('favoritos', [
            'user_id' => $user->id,
            'coche_id' => $coche->id,
        ]);
    }

    public function test_favorites_page_lists_user_favorites(): void
    {
        $user = User::factory()->create();
        $coche = Coche::factory()->create(['title' => 'Audi Q5 2022']);
        $user->favoritos()->create(['coche_id' => $coche->id]);

        $this->actingAs($user)
            ->get(route('perfil.favoritos'))
            ->assertOk()
            ->assertSee('Audi Q5 2022');
    }

    public function test_owner_can_delete_listing(): void
    {
        $user = User::factory()->create();
        $coche = Coche::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('coches.destroy', $coche))
            ->assertRedirect(route('coches.mine'));

        $this->assertDatabaseMissing('coches', ['id' => $coche->id]);
    }
}
