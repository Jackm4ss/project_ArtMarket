<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\WalletLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_requires_valid_signature(): void
    {
        config()->set('services.midtrans.server_key', 'secret-key');

        $this->postJson(route('webhooks.midtrans'), [
            'order_id' => 'AM-INVALID',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'invalid',
        ])->assertForbidden();
    }

    public function test_paid_webhook_is_idempotent_and_records_escrow_once(): void
    {
        config()->set('services.midtrans.server_key', 'secret-key');

        $product = Product::factory()->create(['price' => 1000000]);
        $order = Order::query()->create([
            'invoice' => 'AM-TEST-001',
            'guest_name' => 'Nadia Kusuma',
            'guest_email' => 'nadia@example.test',
            'guest_phone' => '081234567890',
            'status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'subtotal' => 1000000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => 100000,
            'grand_total' => 1000000,
            'currency' => 'IDR',
            'idempotency_key' => 'order-test-001',
            'shipping_snapshot' => ['name' => 'Nadia Kusuma'],
        ]);
        $order->items()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => 1,
            'unit_price' => 1000000,
            'subtotal' => 1000000,
            'commission_amount' => 100000,
            'status' => OrderStatus::PendingPayment,
        ]);
        $payment = $order->payments()->create([
            'invoice' => $order->invoice,
            'gateway' => 'midtrans',
            'gateway_reference' => 'trx-001',
            'status' => PaymentStatus::Pending,
            'amount' => 1000000,
            'currency' => 'IDR',
            'idempotency_key' => 'payment-test-001',
        ]);

        $payload = $this->signedPayload($order->invoice, 'trx-001', 'settlement', '1000000.00');

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertDatabaseCount('payment_events', 1);
        $this->assertSame(1, WalletLedger::query()->where('type', 'escrow_pending')->count());
        $this->assertSame(1, WalletLedger::query()->where('type', 'commission_recorded')->count());
    }

    public function test_expired_webhook_cancels_order_and_releases_stock_once(): void
    {
        config()->set('services.midtrans.server_key', 'secret-key');

        $product = Product::factory()->create(['price' => 1000000, 'stock' => 1]);
        $order = Order::query()->create([
            'invoice' => 'AM-EXP-001',
            'guest_name' => 'Nadia Kusuma',
            'guest_email' => 'nadia@example.test',
            'guest_phone' => '081234567890',
            'status' => OrderStatus::PendingPayment,
            'payment_status' => PaymentStatus::Pending,
            'subtotal' => 2000000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => 200000,
            'grand_total' => 2000000,
            'currency' => 'IDR',
            'idempotency_key' => 'order-exp-001',
            'shipping_snapshot' => ['name' => 'Nadia Kusuma'],
        ]);
        $order->items()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => 2,
            'unit_price' => 1000000,
            'subtotal' => 2000000,
            'commission_amount' => 200000,
            'status' => OrderStatus::PendingPayment,
        ]);
        $order->payments()->create([
            'invoice' => $order->invoice,
            'gateway' => 'midtrans',
            'gateway_reference' => 'trx-exp-001',
            'status' => PaymentStatus::Pending,
            'amount' => 2000000,
            'currency' => 'IDR',
            'idempotency_key' => 'payment-exp-001',
        ]);

        $payload = $this->signedPayload($order->invoice, 'trx-exp-001', 'expire', '2000000.00');

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Expired, $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->stock_released_at);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('payment_events', 1);
    }

    /**
     * @return array<string, string>
     */
    private function signedPayload(string $invoice, string $transactionId, string $status, string $grossAmount): array
    {
        $statusCode = '200';
        $serverKey = 'secret-key';

        return [
            'order_id' => $invoice,
            'transaction_id' => $transactionId,
            'transaction_status' => $status,
            'fraud_status' => 'accept',
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $invoice.$statusCode.$grossAmount.$serverKey),
        ];
    }
}
