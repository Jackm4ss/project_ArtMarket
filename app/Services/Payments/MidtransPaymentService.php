<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MidtransPaymentService
{
    public function createInvoice(Order $order): Payment
    {
        $existing = $order->payments()->latest('id')->first();

        if ($existing) {
            return $existing;
        }

        $serverKey = (string) config('services.midtrans.server_key');

        if ($serverKey === '') {
            return $this->createLocalFallback($order);
        }

        MidtransConfig::$serverKey = $serverKey;
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production');
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        $itemDetails = $order->items->map(fn ($item): array => [
            'id' => (string) ($item->product_id ?? $item->id),
            'price' => (int) round((float) $item->unit_price),
            'quantity' => (int) $item->quantity,
            'name' => Str::limit($item->product_title, 48, ''),
        ])->values();

        if ((float) $order->discount_total > 0) {
            $itemDetails->push([
                'id' => 'voucher-'.$order->voucher_id,
                'price' => -1 * (int) round((float) $order->discount_total),
                'quantity' => 1,
                'name' => 'Diskon voucher',
            ]);
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->invoice,
                'gross_amount' => (int) round((float) $order->grand_total),
            ],
            'customer_details' => [
                'first_name' => $order->guest_name,
                'email' => $order->guest_email,
                'phone' => $order->guest_phone,
            ],
            'item_details' => $itemDetails->all(),
        ];

        $transaction = Snap::createTransaction($payload);

        return $order->payments()->create([
            'invoice' => $order->invoice,
            'gateway' => 'midtrans',
            'gateway_reference' => $transaction->token ?? null,
            'status' => PaymentStatus::Pending,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'raw_payload' => [
                'token' => $transaction->token ?? null,
                'redirect_url' => $transaction->redirect_url ?? null,
                'request' => $payload,
            ],
            'idempotency_key' => 'payment-'.$order->id,
        ]);
    }

    private function createLocalFallback(Order $order): Payment
    {
        return $order->payments()->create([
            'invoice' => $order->invoice,
            'gateway' => 'local-fallback',
            'gateway_reference' => 'local-'.$order->invoice,
            'status' => PaymentStatus::Pending,
            'amount' => $order->grand_total,
            'currency' => $order->currency,
            'raw_payload' => [
                'provider' => 'local-fallback',
                'redirect_url' => URL::route('payments.show', $order),
                'message' => 'MIDTRANS_SERVER_KEY belum diisi; invoice lokal dibuat untuk development.',
            ],
            'idempotency_key' => 'payment-'.$order->id,
        ]);
    }
}
