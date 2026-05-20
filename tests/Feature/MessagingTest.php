<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
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
        $listing = Listing::factory()->for($seller)->create();

        $this->actingAs($buyer)
            ->post(route('listings.contact', $listing), ['body' => 'Hola, ¿sigue disponible?'])
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $buyer->id,
            'body' => 'Hola, ¿sigue disponible?',
        ]);
    }

    public function test_starting_a_second_conversation_reuses_the_existing_one(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();

        $this->actingAs($buyer)->post(route('listings.contact', $listing), ['body' => 'Mensaje 1']);
        $this->actingAs($buyer)->post(route('listings.contact', $listing), ['body' => 'Mensaje 2']);

        $this->assertSame(1, Conversation::count());
        $this->assertSame(2, Message::count());
    }

    public function test_owner_cannot_contact_themselves(): void
    {
        $seller = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();

        $this->actingAs($seller)
            ->post(route('listings.contact', $listing), ['body' => 'Hola'])
            ->assertForbidden();
    }

    public function test_third_party_cannot_view_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $stranger = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();
        $conv = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($stranger)
            ->get(route('account.messages.show', $conv))
            ->assertForbidden();
    }

    public function test_admin_can_view_any_conversation(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $listing = Listing::factory()->for($seller)->create();
        $conv = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($admin)
            ->get(route('account.messages.show', $conv))
            ->assertOk();
    }

    public function test_viewing_conversation_marks_incoming_as_read(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();
        $conv = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $msgFromBuyer = $conv->messages()->create([
            'sender_id' => $buyer->id,
            'body' => 'Hola',
        ]);

        $this->assertNull($msgFromBuyer->read_at);

        $this->actingAs($seller)
            ->get(route('account.messages.show', $conv))
            ->assertOk();

        $this->assertNotNull($msgFromBuyer->fresh()->read_at);
    }

    public function test_seller_can_reply(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();
        $conv = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($seller)
            ->post(route('account.messages.reply', $conv), ['body' => 'Sí, sigue disponible.'])
            ->assertRedirect(route('account.messages.show', $conv));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'sender_id' => $seller->id,
            'body' => 'Sí, sigue disponible.',
        ]);
    }

    public function test_message_creation_updates_last_message_at(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();
        $conv = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);

        $this->assertNull($conv->last_message_at);

        $conv->messages()->create(['sender_id' => $buyer->id, 'body' => 'Hola']);

        $this->assertNotNull($conv->fresh()->last_message_at);
    }

    public function test_unread_count_helper_on_user(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $listing = Listing::factory()->for($seller)->create();
        $conv = Conversation::create([
            'listing_id' => $listing->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
        ]);
        $conv->messages()->create(['sender_id' => $buyer->id, 'body' => 'A']);
        $conv->messages()->create(['sender_id' => $buyer->id, 'body' => 'B']);
        $conv->messages()->create(['sender_id' => $seller->id, 'body' => 'mine']);

        $this->assertSame(2, $seller->fresh()->unreadMessagesCount());
        $this->assertSame(1, $buyer->fresh()->unreadMessagesCount());
    }
}
