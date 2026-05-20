<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\Role;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_gets_403(): void
    {
        $user = User::factory()->create(['role' => Role::User]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_sees_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory(3)->create();
        Listing::factory(5)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Panel de administración');
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create(['name' => 'Pepito Pérez']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pepito Pérez');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $other), [
                'name' => 'Nuevo Nombre',
                'email' => $other->email,
                'phone' => '600111222',
                'province' => 'Sevilla',
                'role' => Role::User->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $other->id,
            'name' => 'Nuevo Nombre',
            'phone' => '600111222',
            'province' => 'Sevilla',
        ]);
    }

    public function test_admin_can_promote_user_to_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $other), [
                'name' => $other->name,
                'email' => $other->email,
                'role' => Role::Admin->value,
            ])
            ->assertRedirect();

        $this->assertSame(Role::Admin, $other->fresh()->role);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $other))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_change_listing_status(): void
    {
        $admin = User::factory()->admin()->create();
        $listing = Listing::factory()->create(['status' => ListingStatus::Active->value]);

        $this->actingAs($admin)
            ->patch(route('admin.listings.status', $listing), ['status' => ListingStatus::Expired->value])
            ->assertRedirect();

        $this->assertSame(ListingStatus::Expired, $listing->fresh()->status);
    }

    public function test_admin_can_toggle_featured(): void
    {
        $admin = User::factory()->admin()->create();
        $listing = Listing::factory()->create(['featured' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.listings.feature', $listing))
            ->assertRedirect();

        $this->assertTrue($listing->fresh()->featured);
    }

    public function test_admin_can_delete_any_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $listing = Listing::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.listings.destroy', $listing))
            ->assertRedirect(route('admin.listings.index'));

        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }
}
