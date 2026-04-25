<?php

namespace App\Http\Controllers\Realtime;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Models\Conversation;
use App\Services\Chat\ChatService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ChatMessageController extends Controller
{
    public function store(SendMessageRequest $request, Conversation $conversation, ChatService $chat): JsonResponse|RedirectResponse
    {
        $message = $chat->sendMessage($request->user(), $conversation, $request->validated('body'));

        if (! $request->expectsJson()) {
            return back()->with('status', 'Pesan terkirim.');
        }

        return response()->json([
            'message' => $chat->serializeMessage($message, $request->user()),
        ], Response::HTTP_CREATED);
    }
}
