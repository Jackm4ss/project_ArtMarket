<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Product;
use App\Services\Chat\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserChatController extends Controller
{
    public function index(Request $request, ChatService $chat): Response
    {
        return Inertia::render('User/Chats', [
            'conversations' => $chat->conversationsForUser($request->user())->values(),
            'activeConversation' => null,
            'messages' => [],
            'realtimeDriver' => config('realtime.driver'),
            'pollingIntervalSeconds' => config('realtime.polling_interval_seconds'),
            'sseBackoffSeconds' => config('realtime.sse.reconnect_backoff_seconds'),
        ]);
    }

    public function show(Request $request, Conversation $conversation, ChatService $chat): Response
    {
        $conversation = $chat->ensureParticipant($request->user(), $conversation);

        return Inertia::render('User/Chats', [
            'conversations' => $chat->conversationsForUser($request->user())->values(),
            'activeConversation' => $chat->serializeConversation($conversation, $request->user(), 0),
            'messages' => $chat->messagesFor($request->user(), $conversation)->values(),
            'realtimeDriver' => config('realtime.driver'),
            'pollingIntervalSeconds' => config('realtime.polling_interval_seconds'),
            'sseBackoffSeconds' => config('realtime.sse.reconnect_backoff_seconds'),
        ]);
    }

    public function startFromProduct(Request $request, Product $product, ChatService $chat): RedirectResponse
    {
        $conversation = $chat->startProductConversation($request->user(), $product);

        return redirect()
            ->route('user.chats.show', $conversation)
            ->with('status', 'Chat dengan seller siap digunakan.');
    }
}
