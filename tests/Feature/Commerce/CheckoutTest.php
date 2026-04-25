<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_creates_order_payment_and_decrements_stock(): void
    {
        config()->set('services.midtrans.server_key', '');

        $product = Product::factory()->create(['stock' => 4, 'price' => 500000]);
        $idempotencyKey = (string) Str::uuid();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->post(route('checkout.store'), $this->checkoutPayload($idempotencyKey))
            ->assertRedirect();

        $order = Order::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();

        $this->assertSame(OrderStatus::PendingPayment, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'gateway' => 'local-fallback',
            'status' => PaymentStatus::Pending->value,
        ]);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_duplicate_checkout_submit_returns_existing_order(): void
    {
        config()->set('services.midtrans.server_key', '');

        $product = Product::factory()->create(['stock' => 4, 'price' => 500000]);
        $idempotencyKey = (string) Str::uuid();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->post(route('checkout.store'), $this->checkoutPayload($idempotencyKey))->assertRedirect();
        $this->post(route('checkout.store'), $this->checkoutPayload($idempotencyKey))->assertRedirect();

        $this->assertSame(1, Order::query()->where('idempotency_key', $idempotencyKey)->count());
        $this->assertSame(1, Payment::query()->count());
        $this->assertSame(3, $product->fresh()->stock);
    }

    /**
     * @return array<string, string>
     */
    private function checkoutPayload(string $idempotencyKey): array
    {
        return [
            'idempotency_key' => $idempotencyKey,
            'name' => 'Nadia Kusuma',
            'email' => 'nadia@example.test',
            'phone' => '081234567890',
            'address' => 'Jl. Seni No. 12',
            'city' => 'Yogyakarta',
            'province' => 'DI Yogyakarta',
            'postal_code' => '55111',
            'voucher_code' => '',
            'notes' => 'Kirim dengan packing kayu.',
        ];
    }
}
