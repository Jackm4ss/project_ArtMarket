<?php

namespace App\Http\Controllers\Realtime;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Realtime\SseStream;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatStreamController extends Controller
{
    public function __invoke(Request $request, Conversation $conversation, SseStream $stream, ChatService $chat): StreamedResponse
    {
        $chat->ensureParticipant($request->user(), $conversation);
        $afterId = max(0, $request->integer('after_id'));

        return $stream->response(function (callable $send) use ($request, $conversation, $chat, $afterId): void {
            $messages = $conversation->messages()
                ->with('sender:id,name')
                ->whereNull('hidden_at')
                ->latest('id')
                ->limit(30)
                ->get()
                ->reverse()
                ->values()
                ->map(fn ($message): array => $chat->serializeMessage($message, $request->user()))
                ->values();

            $lastMessageId = max($afterId, (int) ($messages->last()['id'] ?? 0));

            $send('messages.snapshot', [
                'conversation_id' => $conversation->id,
                'messages' => $messages,
                'server_time' => now()->toIso8601String(),
            ]);

            $startedAt = time();
            $lastHeartbeatAt = time();
            $maxSeconds = (int) config('realtime.sse.max_execution_seconds', 55);
            $heartbeatSeconds = (int) config('realtime.sse.heartbeat_seconds', 15);

            while ((time() - $startedAt) < $maxSeconds && ! connection_aborted()) {
                sleep(1);

                $newMessages = $conversation->messages()
                    ->with('sender:id,name')
                    ->whereNull('hidden_at')
                    ->where('id', '>', $lastMessageId)
                    ->orderBy('id')
                    ->limit(50)
                    ->get()
                    ->map(fn ($message): array => $chat->serializeMessage($message, $request->user()))
                    ->values();

                if ($newMessages->isNotEmpty()) {
                    $lastMessageId = (int) $newMessages->last()['id'];

                    $send('messages.append', [
                        'conversation_id' => $conversation->id,
                        'messages' => $newMessages,
                        'server_time' => now()->toIso8601String(),
                    ]);
                }

                if ((time() - $lastHeartbeatAt) >= $heartbeatSeconds) {
                    $lastHeartbeatAt = time();

                    $send('heartbeat', [
                        'conversation_id' => $conversation->id,
                        'server_time' => now()->toIso8601String(),
                    ]);
                }
            }
        });
    }
}
