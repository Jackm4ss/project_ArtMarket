<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Services\Notifications\MarketplaceNotificationService;
use App\Services\Orders\OrderResolutionService;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransWebhookHandler
{
    public function __construct(
        private readonly WalletLedgerService $walletLedgers,
        private readonly OrderResolutionService $orderResolution,
        private readonly MarketplaceNotificationService $notifications,
    )
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function signatureIsValid(array $payload): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            return false;
        }

        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signature === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expected, $signature);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload): Payment
    {
        return DB::transaction(function () use ($payload): Payment {
            $invoice = (string) ($payload['order_id'] ?? '');
            $gatewayReference = (string) ($payload['transaction_id'] ?? '');
            $eventType = (string) ($payload['transaction_status'] ?? 'unknown');
            $idempotencyKey = $this->idempotencyKey($payload);

            $payment = Payment::query()
                ->with(['order.items'])
                ->where('invoice', $invoice)
                ->when($gatewayReference !== '', fn ($query) => $query->orWhere('gateway_reference', $gatewayReference))
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                throw new RuntimeException('Payment invoice tidak ditemukan.');
            }

            $event = PaymentEvent::query()->firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'payment_id' => $payment->id,
                    'gateway_reference' => $gatewayReference ?: $payment->gateway_reference,
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'processed_at' => now(),
                ]
            );

            if (! $event->wasRecentlyCreated) {
                return $payment;
            }

            if ($gatewayReference !== '' && $payment->gateway_reference !== $gatewayReference) {
                $payment->gateway_reference = $gatewayReference;
            }

            $nextStatus = $this->mapStatus($payload);
            $payment->status = $nextStatus;

            if ($nextStatus === PaymentStatus::Paid && ! $payment->paid_at) {
                $payment->paid_at = now();
            }

            $payment->raw_payload = array_merge($payment->raw_payload ?? [], [
                'last_webhook' => $payload,
            ]);
            $payment->save();

            $order = $payment->order;
            $order->payment_status = $nextStatus;

            if ($nextStatus === PaymentStatus::Paid && $order->status === OrderStatus::PendingPayment) {
                $order->status = OrderStatus::Paid;
                $order->items()->update(['status' => OrderStatus::Paid->value]);
            }

            if ($nextStatus === PaymentStatus::Expired) {
                $order->status = OrderStatus::Cancelled;
            }

            if ($nextStatus === PaymentStatus::Failed && $order->status === OrderStatus::PendingPayment) {
                $order->status = OrderStatus::Cancelled;
            }

            $order->save();

            if ($nextStatus === PaymentStatus::Paid) {
                $this->walletLedgers->recordEscrowPending($order);
                $this->notifications->orderPaid($order->fresh(['user', 'items.seller.user']));
            }

            if ($nextStatus === PaymentStatus::Expired || $nextStatus === PaymentStatus::Failed) {
                $this->orderResolution->cancelUnpaid($order, adminNote: "Payment {$nextStatus->value} dari Midtrans.");
            }

            if ($nextStatus === PaymentStatus::Refunded) {
                $this->orderResolution->approveRefund($order, 'Refund dikonfirmasi oleh Midtrans webhook.');
            }

            return $payment->fresh(['order.items']);
        });
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function mapStatus(array $payload): PaymentStatus
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? 'pending');
        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');

        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? PaymentStatus::Paid : PaymentStatus::Pending;
        }

        return match ($transactionStatus) {
            'settlement' => PaymentStatus::Paid,
            'expire' => PaymentStatus::Expired,
            'deny', 'cancel', 'failure' => PaymentStatus::Failed,
            'refund', 'partial_refund' => PaymentStatus::Refunded,
            default => PaymentStatus::Pending,
        };
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function idempotencyKey(array $payload): string
    {
        $parts = [
            $payload['order_id'] ?? '',
            $payload['transaction_id'] ?? '',
            $payload['transaction_status'] ?? '',
            $payload['fraud_status'] ?? '',
            $payload['status_code'] ?? '',
        ];

        return 'midtrans-'.Str::of(implode('|', $parts))->replace(' ', '-')->lower()->limit(180, '')->toString();
    }
}
