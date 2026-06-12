<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Coche;
use App\Models\Mensaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_start_conversation_from_listing(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();

        $this->actingAs($buyer)
            ->post(route('coches.contact', $coche), ['body' => 'Hola, ¿sigue disponible?'])
            ->assertRedirect();

        $this->assertDatabaseHas('chats', [
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $this->assertDatabaseHas('mensajes', [
            'sender_id' => $buyer->id,
            'body' => 'Hola, ¿sigue disponible?',
        ]);
    }

    public function test_starting_a_second_conversation_reuses_the_existing_one(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();

        $this->actingAs($buyer)->post(route('coches.contact', $coche), ['body' => 'Mensaje 1']);
        $this->actingAs($buyer)->post(route('coches.contact', $coche), ['body' => 'Mensaje 2']);

        $this->assertSame(1, Chat::count());
        $this->assertSame(2, Mensaje::count());
    }

    public function test_owner_cannot_contact_themselves(): void
    {
        $seller = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();

        $this->actingAs($seller)
            ->post(route('coches.contact', $coche), ['body' => 'Hola'])
            ->assertForbidden();
    }

    public function test_third_party_cannot_view_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();
        $conv = Chat::create([
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($stranger)
            ->get(route('chats.show', $conv))
            ->assertForbidden();
    }

    public function test_admin_can_view_any_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $coche = Coche::factory()->for($seller)->create();
        $conv = Chat::create([
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($admin)
            ->get(route('chats.show', $conv))
            ->assertOk();
    }

    public function test_viewing_conversation_marks_incoming_as_read(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();
        $conv = Chat::create([
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $msgFromBuyer = $conv->mensajes()->create([
            'sender_id' => $buyer->id,
            'body' => 'Hola',
        ]);

        $this->assertNull($msgFromBuyer->read_at);

        $this->actingAs($seller)
            ->get(route('chats.show', $conv))
            ->assertOk();

        $this->assertNotNull($msgFromBuyer->fresh()->read_at);
    }

    public function test_seller_can_reply(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();
        $conv = Chat::create([
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->post(route('chats.reply', $conv), ['body' => 'Sí, sigue disponible.'])
            ->assertRedirect(route('chats.show', $conv));

        $this->assertDatabaseHas('mensajes', [
            'chat_id' => $conv->id,
            'sender_id' => $seller->id,
            'body' => 'Sí, sigue disponible.',
        ]);
    }

    public function test_message_creation_updates_last_message_at(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();
        $conv = Chat::create([
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertNull($conv->last_message_at);

        $conv->mensajes()->create(['sender_id' => $buyer->id, 'body' => 'Hola']);

        $this->assertNotNull($conv->fresh()->last_message_at);
    }

    public function test_unread_count_helper_on_user(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $coche = Coche::factory()->for($seller)->create();
        $conv = Chat::create([
            'coche_id' => $coche->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $conv->mensajes()->create(['sender_id' => $buyer->id, 'body' => 'A']);
        $conv->mensajes()->create(['sender_id' => $buyer->id, 'body' => 'B']);
        $conv->mensajes()->create(['sender_id' => $seller->id, 'body' => 'mine']);

        $this->assertSame(2, $seller->fresh()->unreadMessagesCount());
        $this->assertSame(1, $buyer->fresh()->unreadMessagesCount());
    }
}
