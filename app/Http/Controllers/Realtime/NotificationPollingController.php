<?php

namespace App\Http\Controllers\Realtime;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPollingController extends Controller
{
    public function __invoke(Request $request, ChatService $chat): JsonResponse
    {
        return response()->json([
            'unread_notifications' => $request->user()->unreadNotifications()->count(),
            'chat_unread' => $chat->unreadCount($request->user()),
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
