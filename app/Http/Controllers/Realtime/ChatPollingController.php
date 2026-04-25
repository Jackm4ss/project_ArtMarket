<?php

namespace App\Http\Controllers\Realtime;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatPollingController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation, ChatService $chat): JsonResponse
    {
        $chat->ensureParticipant($request->user(), $conversation);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->whereNull('hidden_at')
            ->when($request->integer('after_id') > 0, fn ($query) => $query->where('id', '>', $request->integer('after_id')))
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn ($message): array => $chat->serializeMessage($message, $request->user()))
            ->values();

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
