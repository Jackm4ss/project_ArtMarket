<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WalletLedger;
use App\Services\Orders\OrderResolutionService;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_cancel_unpaid_order_and_stock_is_released_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);
        $order = $this->createOrder($user, $product, OrderStatus::PendingPayment, PaymentStatus::Pending, quantity: 2);

        $this->actingAs($user)
            ->patch(route('user.orders.cancel', $order), ['note' => 'Salah alamat'])
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('user.orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->stock_released_at);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(OrderStatus::Cancelled, $order->items()->firstOrFail()->status);
    }

    public function test_user_can_request_refund_and_admin_can_reject_it(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 0]);
        $order = $this->createOrder($user, $product, OrderStatus::Shipped, PaymentStatus::Paid);

        $this->actingAs($user)
            ->patch(route('user.orders.refund-request', $order), ['note' => 'Karya rusak saat tiba'])
            ->assertRedirect();

        $this->assertSame(OrderStatus::RefundRequested, $order->fresh()->status);
        $this->assertSame(OrderStatus::Shipped->value, $order->fresh()->status_before_refund);

        app(OrderResolutionService::class)->rejectRefund($order->fresh(), 'Bukti belum cukup.');

        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_admin_refund_completed_order_debits_available_seller_balance_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000000, 'stock' => 0]);
        $order = $this->createOrder($user, $product, OrderStatus::Completed, PaymentStatus::Paid);

        app(WalletLedgerService::class)->recordEscrowPending($order);
        app(WalletLedgerService::class)->releaseEscrow($order);

        $this->assertSame(900000.0, app(WalletLedgerService::class)->availableBalance($product->seller_id));

        app(OrderResolutionService::class)->requestRefund($order, 'Pembeli mengajukan refund.');
        app(OrderResolutionService::class)->approveRefund($order->fresh(), 'Refund disetujui admin.');
        app(OrderResolutionService::class)->approveRefund($order->fresh(), 'Refund disetujui admin.');

        $this->assertSame(OrderStatus::Refunded, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Refunded, $order->fresh()->payment_status);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(1, WalletLedger::query()->where('order_id', $order->id)->where('type', 'refund_debited')->count());
        $this->assertSame(1, WalletLedger::query()->where('order_id', $order->id)->where('type', 'refund_recorded')->count());
        $this->assertSame(0.0, app(WalletLedgerService::class)->availableBalance($product->seller_id));
    }

    private function createOrder(User $user, Product $product, OrderStatus $status, PaymentStatus $paymentStatus, int $quantity = 1): Order
    {
        $subtotal = (float) $product->price * $quantity;
        $commission = round($subtotal * 0.10, 2);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'invoice' => 'AM-RES-'.fake()->unique()->numerify('####'),
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'guest_phone' => '081234567890',
            'status' => $status,
            'payment_status' => $paymentStatus,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => $commission,
            'grand_total' => $subtotal,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'shipping_snapshot' => ['name' => $user->name],
        ]);

        $order->items()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => $quantity,
            'unit_price' => (float) $product->price,
            'subtotal' => $subtotal,
            'commission_amount' => $commission,
            'status' => $status,
        ]);

        $order->payments()->create([
            'invoice' => $order->invoice,
            'gateway' => 'midtrans',
            'gateway_reference' => 'trx-'.$order->id,
            'status' => $paymentStatus,
            'amount' => $subtotal,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'paid_at' => $paymentStatus === PaymentStatus::Paid ? now() : null,
        ]);

        return $order;
    }
}
