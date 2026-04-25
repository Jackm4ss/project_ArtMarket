<?php

namespace App\Services\Chat;

use App\Enums\ChatMessageStatus;
use App\Enums\ProductStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\Notifications\MarketplaceNotificationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChatService
{
    public function __construct(private readonly MarketplaceNotificationService $notifications)
    {
    }

    public function startProductConversation(User $buyer, Product $product): Conversation
    {
        $product->loadMissing('seller.user');

        if ($product->status !== ProductStatus::Published) {
            throw ValidationException::withMessages([
                'product' => 'Produk tidak tersedia untuk chat.',
            ]);
        }

        $seller = $product->seller;

        if (! $seller || ! $seller->user_id) {
            throw ValidationException::withMessages([
                'seller' => 'Seller produk ini belum siap menerima chat.',
            ]);
        }

        if ($seller->user_id === $buyer->id) {
            throw ValidationException::withMessages([
                'seller' => 'Anda tidak bisa membuka chat dengan toko sendiri.',
            ]);
        }

        return DB::transaction(function () use ($buyer, $seller, $product): Conversation {
            $conversation = Conversation::query()
                ->where('buyer_id', $buyer->id)
                ->where('seller_id', $seller->id)
                ->lockForUpdate()
                ->first();

            if (! $conversation) {
                $conversation = Conversation::query()->create([
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                    'product_id' => $product->id,
                    'last_message_at' => now(),
                ]);
            } elseif (! $conversation->product_id) {
                $conversation->forceFill(['product_id' => $product->id])->save();
            }

            $conversation->participants()->updateOrCreate(
                ['user_id' => $buyer->id],
                ['role' => 'buyer']
            );

            $conversation->participants()->updateOrCreate(
                ['user_id' => $seller->user_id],
                ['role' => 'seller']
            );

            return $conversation->load($this->conversationRelations());
        });
    }

    public function ensureParticipant(User $user, Conversation $conversation): Conversation
    {
        $isParticipant = $conversation->participants()
            ->where('user_id', $user->id)
            ->exists();

        abort_unless($isParticipant, 403);

        return $conversation->loadMissing($this->conversationRelations());
    }

    public function ensureSellerParticipant(User $user, Conversation $conversation): Conversation
    {
        $seller = $user->seller;

        abort_unless($seller && $conversation->seller_id === $seller->id, 403);

        return $this->ensureParticipant($user, $conversation);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function conversationsForUser(User $user): Collection
    {
        $conversations = Conversation::query()
            ->with($this->conversationRelations())
            ->whereHas('participants', fn ($query) => $query->where('user_id', $user->id))
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get();

        return $this->serializeConversations($conversations, $user);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function conversationsForSeller(Seller $seller): Collection
    {
        $sellerUser = $seller->user;

        if (! $sellerUser) {
            return collect();
        }

        $conversations = Conversation::query()
            ->with($this->conversationRelations())
            ->where('seller_id', $seller->id)
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC')
            ->get();

        return $this->serializeConversations($conversations, $sellerUser);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function messagesFor(User $user, Conversation $conversation, bool $markAsRead = true): Collection
    {
        $this->ensureParticipant($user, $conversation);

        if ($markAsRead) {
            $this->markAsRead($user, $conversation);
        }

        return $conversation->messages()
            ->with('sender:id,name')
            ->whereNull('hidden_at')
            ->oldest('id')
            ->limit(100)
            ->get()
            ->map(fn (Message $message): array => $this->serializeMessage($message, $user))
            ->values();
    }

    public function sendMessage(User $sender, Conversation $conversation, string $body): Message
    {
        $this->ensureParticipant($sender, $conversation);

        return DB::transaction(function () use ($sender, $conversation, $body): Message {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'body' => trim($body),
                'status' => ChatMessageStatus::Sent,
            ]);

            $conversation->forceFill(['last_message_at' => now()])->save();
            $this->notifications->chatMessageSent($message->load(['sender', 'conversation.participants.user']));

            return $message->load('sender:id,name');
        });
    }

    public function markAsRead(User $user, Conversation $conversation): void
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->first();

        if (! $participant) {
            return;
        }

        DB::transaction(function () use ($user, $conversation, $participant): void {
            $now = now();

            $participant->forceFill(['last_read_at' => $now])->save();

            $conversation->messages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update([
                    'read_at' => $now,
                    'status' => ChatMessageStatus::Read,
                ]);
        });
    }

    public function unreadCount(User $user, ?Conversation $conversation = null): int
    {
        $query = Message::query()
            ->join('conversation_participants', 'conversation_participants.conversation_id', '=', 'messages.conversation_id')
            ->where('conversation_participants.user_id', $user->id)
            ->where('messages.sender_id', '!=', $user->id)
            ->whereNull('messages.hidden_at')
            ->whereNull('messages.deleted_at')
            ->where(function ($query): void {
                $query
                    ->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            });

        if ($conversation) {
            $query->where('messages.conversation_id', $conversation->id);
        }

        return (int) $query->count('messages.id');
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeConversation(Conversation $conversation, User $viewer, ?int $unreadCount = null): array
    {
        $conversation->loadMissing($this->conversationRelations());
        $latestMessage = $conversation->latestMessage;

        return [
            'id' => $conversation->id,
            'buyer' => $conversation->buyer ? [
                'id' => $conversation->buyer->id,
                'name' => $conversation->buyer->name,
            ] : null,
            'seller' => $conversation->seller ? [
                'id' => $conversation->seller->id,
                'store_name' => $conversation->seller->store_name,
                'slug' => $conversation->seller->slug,
                'location' => $conversation->seller->location,
            ] : null,
            'product' => $conversation->product ? [
                'id' => $conversation->product->id,
                'title' => $conversation->product->title,
                'slug' => $conversation->product->slug,
            ] : null,
            'counterpart' => $viewer->id === $conversation->buyer_id
                ? ($conversation->seller?->store_name ?? 'Seller')
                : ($conversation->buyer?->name ?? 'Pembeli'),
            'last_message_at' => $conversation->last_message_at?->toIso8601String(),
            'last_message' => $latestMessage ? $this->serializeMessage($latestMessage, $viewer) : null,
            'unread_count' => $unreadCount ?? $this->unreadCount($viewer, $conversation),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeMessage(Message $message, User $viewer): array
    {
        $message->loadMissing('sender:id,name');

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'body' => $message->body,
            'status' => $message->status->value,
            'read_at' => $message->read_at?->toIso8601String(),
            'created_at' => $message->created_at?->toIso8601String(),
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
            ] : null,
            'is_mine' => $message->sender_id === $viewer->id,
        ];
    }

    /**
     * @param EloquentCollection<int, Conversation> $conversations
     * @return Collection<int, array<string, mixed>>
     */
    private function serializeConversations(EloquentCollection $conversations, User $viewer): Collection
    {
        $unreadCounts = $this->unreadCountsByConversation($viewer);

        return $conversations
            ->map(fn (Conversation $conversation): array => $this->serializeConversation(
                $conversation,
                $viewer,
                $unreadCounts[$conversation->id] ?? 0,
            ))
            ->values();
    }

    /**
     * @return array<int, int>
     */
    private function unreadCountsByConversation(User $user): array
    {
        return Message::query()
            ->selectRaw('messages.conversation_id, COUNT(messages.id) as aggregate')
            ->join('conversation_participants', 'conversation_participants.conversation_id', '=', 'messages.conversation_id')
            ->where('conversation_participants.user_id', $user->id)
            ->where('messages.sender_id', '!=', $user->id)
            ->whereNull('messages.hidden_at')
            ->whereNull('messages.deleted_at')
            ->where(function ($query): void {
                $query
                    ->whereNull('conversation_participants.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_participants.last_read_at');
            })
            ->groupBy('messages.conversation_id')
            ->pluck('aggregate', 'conversation_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function conversationRelations(): array
    {
        return [
            'buyer:id,name',
            'seller:id,user_id,store_name,slug,location',
            'seller.user:id,name',
            'product:id,title,slug',
            'latestMessage.sender:id,name',
        ];
    }
}
