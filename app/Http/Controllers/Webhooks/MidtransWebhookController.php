<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\MidtransWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransWebhookHandler $handler): JsonResponse
    {
        $payload = $request->all();

        if (! $handler->signatureIsValid($payload)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        try {
            $payment = $handler->handle($payload);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json([
            'message' => 'Webhook processed.',
            'payment_id' => $payment->id,
            'status' => $payment->status->value,
        ]);
    }
}
