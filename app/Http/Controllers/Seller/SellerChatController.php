<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Chat\ChatService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SellerChatController extends Controller
{
    public function index(Request $request, ChatService $chat): View
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        return view('seller.chats', [
            'conversations' => $chat->conversationsForSeller($seller),
            'activeConversation' => null,
            'messages' => collect(),
        ]);
    }

    public function show(Request $request, Conversation $conversation, ChatService $chat): View
    {
        $conversation = $chat->ensureSellerParticipant($request->user(), $conversation);

        return view('seller.chats', [
            'conversations' => $chat->conversationsForSeller($request->user()->seller),
            'activeConversation' => $chat->serializeConversation($conversation, $request->user(), 0),
            'messages' => $chat->messagesFor($request->user(), $conversation),
        ]);
    }
}
