<?php

namespace Tests\Feature;

use App\Enums\BodyType;
use App\Enums\FuelType;
use App\Enums\ListingStatus;
use App\Enums\Role;
use App\Enums\Transmission;
use App\Models\Listing;
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
            ->get(route('account.dashboard'))
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
            ->post(route('account.listings.store'), $payload)
            ->assertRedirect(route('account.listings.index'));

        $this->assertDatabaseHas('listings', [
            'user_id' => $user->id,
            'title' => 'BMW Serie 3 2020',
            'status' => ListingStatus::Active->value,
        ]);

        $listing = Listing::firstWhere('title', 'BMW Serie 3 2020');
        $this->assertCount(1, $listing->images);
        Storage::disk('public')->assertExists($listing->images->first()->path);
    }

    public function test_user_cannot_edit_others_listing(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $listing = Listing::factory()->for($owner)->create();

        $this->actingAs($other)
            ->get(route('account.listings.edit', $listing))
            ->assertForbidden();
    }

    public function test_admin_can_edit_any_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $listing = Listing::factory()->create();

        $this->actingAs($admin)
            ->get(route('account.listings.edit', $listing))
            ->assertOk();
    }

    public function test_owner_can_mark_listing_as_sold(): void
    {
        $owner = User::factory()->create();
        $listing = Listing::factory()->for($owner)->create([
            'status' => ListingStatus::Active->value,
        ]);

        $this->actingAs($owner)
            ->patch(route('account.listings.mark-sold', $listing))
            ->assertRedirect();

        $this->assertSame(ListingStatus::Sold, $listing->fresh()->status);
    }

    public function test_favorite_toggle(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create();

        $this->actingAs($user)
            ->post(route('listings.favorite', $listing))
            ->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);

        $this->actingAs($user)
            ->post(route('listings.favorite', $listing))
            ->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);
    }

    public function test_favorites_page_lists_user_favorites(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['title' => 'Audi Q5 2022']);
        $user->favorites()->create(['listing_id' => $listing->id]);

        $this->actingAs($user)
            ->get(route('account.favorites.index'))
            ->assertOk()
            ->assertSee('Audi Q5 2022');
    }

    public function test_owner_can_delete_listing(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('account.listings.destroy', $listing))
            ->assertRedirect(route('account.listings.index'));

        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }
}
