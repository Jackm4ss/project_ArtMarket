<?php

namespace App\Realtime;

use Closure;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseStream
{
    public function response(Closure $producer): StreamedResponse
    {
        return response()->stream(function () use ($producer): void {
            echo 'retry: '.config('realtime.sse.retry_milliseconds')."\n\n";
            $this->flush();

            $producer(function (string $event, array $payload): void {
                $this->send($event, $payload);
            });
        }, 200, [
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function send(string $event, array $payload): void
    {
        echo 'event: '.$event."\n";
        echo 'data: '.json_encode($payload, JSON_THROW_ON_ERROR)."\n\n";

        $this->flush();
    }

    private function flush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
