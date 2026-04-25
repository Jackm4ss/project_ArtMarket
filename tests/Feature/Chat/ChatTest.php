<?php

namespace Tests\Feature\Chat;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Product;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_one_conversation_from_product(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($buyer)
            ->post(route('products.chat.start', $product))
            ->assertRedirect();

        $this->actingAs($buyer)
            ->post(route('products.chat.start', $product))
            ->assertRedirect();

        $conversation = Conversation::query()->firstOrFail();

        $this->assertSame(1, Conversation::query()->count());
        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->assertSame($product->seller_id, $conversation->seller_id);
        $this->assertSame($product->id, $conversation->product_id);
        $this->assertSame(2, ConversationParticipant::query()->where('conversation_id', $conversation->id)->count());
    }

    public function test_seller_cannot_start_chat_with_own_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($product->seller->user)
            ->post(route('products.chat.start', $product))
            ->assertSessionHasErrors('seller');

        $this->assertSame(0, Conversation::query()->count());
    }

    public function test_user_can_view_own_chat_and_intruder_is_forbidden(): void
    {
        $buyer = User::factory()->create();
        $intruder = User::factory()->create();
        $conversation = $this->createConversation($buyer);

        $conversation->messages()->create([
            'sender_id' => $conversation->seller->user_id,
            'body' => 'Halo, karya ini masih tersedia.',
            'status' => 'sent',
        ]);

        $this->actingAs($buyer)
            ->get(route('user.chats.show', $conversation))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('User/Chats')
                ->where('activeConversation.id', $conversation->id)
                ->where('realtimeDriver', 'polling')
                ->where('messages.0.body', 'Halo, karya ini masih tersedia.')
            );

        $this->assertNotNull(
            ConversationParticipant::query()
                ->where('conversation_id', $conversation->id)
                ->where('user_id', $buyer->id)
                ->firstOrFail()
                ->last_read_at
        );

        $this->actingAs($intruder)
            ->get(route('user.chats.show', $conversation))
            ->assertForbidden();
    }

    public function test_participants_can_send_messages_and_unread_count_is_cleared_on_open(): void
    {
        Role::findOrCreate('seller');

        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $sellerUser = $product->seller->user;
        $sellerUser->assignRole('seller');
        $conversation = $this->createConversation($buyer, $product);

        $this->actingAs($buyer)
            ->post(route('chats.messages.store', $conversation), ['body' => 'Apakah bisa dikirim minggu ini?'])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $buyer->id,
            'body' => 'Apakah bisa dikirim minggu ini?',
        ]);

        $this->actingAs($sellerUser)
            ->getJson(route('polling.notifications'))
            ->assertOk()
            ->assertJsonPath('chat_unread', 1);

        $this->actingAs($sellerUser)
            ->get(route('seller.chats.show', $conversation))
            ->assertOk();

        $this->actingAs($sellerUser)
            ->getJson(route('polling.notifications'))
            ->assertOk()
            ->assertJsonPath('chat_unread', 0);
    }

    public function test_non_participant_cannot_send_message(): void
    {
        $buyer = User::factory()->create();
        $intruder = User::factory()->create();
        $conversation = $this->createConversation($buyer);

        $this->actingAs($intruder)
            ->postJson(route('chats.messages.store', $conversation), ['body' => 'Saya bukan peserta chat.'])
            ->assertForbidden();
    }

    private function createConversation(User $buyer, ?Product $product = null): Conversation
    {
        return app(ChatService::class)->startProductConversation(
            $buyer,
            $product ?? Product::factory()->create()
        );
    }
}
