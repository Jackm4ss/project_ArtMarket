<?php

namespace App\Http\Controllers\Realtime;

use App\Http\Controllers\Controller;
use App\Realtime\SseStream;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationStreamController extends Controller
{
    public function __invoke(Request $request, SseStream $stream, ChatService $chat): StreamedResponse
    {
        return $stream->response(function (callable $send) use ($request, $chat): void {
            $send('snapshot', [
                'unread_notifications' => $request->user()->unreadNotifications()->count(),
                'chat_unread' => $chat->unreadCount($request->user()),
                'server_time' => now()->toIso8601String(),
            ]);
        });
    }
}
