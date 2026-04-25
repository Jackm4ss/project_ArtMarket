<?php

namespace Tests\Feature\Seller;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_view_only_own_paid_shipments(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $ownProduct = Product::factory()->create(['seller_id' => $seller->id]);
        $otherProduct = Product::factory()->create();

        $ownOrder = $this->createOrder($ownProduct, paymentStatus: PaymentStatus::Paid);
        $otherOrder = $this->createOrder($otherProduct, paymentStatus: PaymentStatus::Paid);
        $unpaidOrder = $this->createOrder($ownProduct, paymentStatus: PaymentStatus::Pending);

        $this->actingAs($sellerUser)
            ->get(route('seller.shipments.index'))
            ->assertOk()
            ->assertSee($ownOrder->invoice)
            ->assertSee($ownProduct->title)
            ->assertDontSee($otherOrder->invoice)
            ->assertDontSee($unpaidOrder->invoice);
    }

    public function test_seller_can_update_shipment_for_own_order_item(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $order = $this->createOrder($product, paymentStatus: PaymentStatus::Paid);
        $item = $order->items()->firstOrFail();

        $this->actingAs($sellerUser)
            ->patch(route('seller.orders.shipment.update', $item), [
                'courier' => 'JNE',
                'tracking_number' => 'JNE123456789',
            ])
            ->assertRedirect();

        $item->refresh();

        $this->assertSame(OrderStatus::Shipped, $item->status);
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
        $this->assertSame('JNE', $item->courier);
        $this->assertSame('JNE123456789', $item->tracking_number);
        $this->assertNotNull($item->shipped_at);
    }

    public function test_seller_cannot_update_another_seller_shipment(): void
    {
        [$sellerUser] = $this->sellerUser();
        $otherProduct = Product::factory()->create();
        $order = $this->createOrder($otherProduct, paymentStatus: PaymentStatus::Paid);
        $item = $order->items()->firstOrFail();

        $this->actingAs($sellerUser)
            ->patch(route('seller.orders.shipment.update', $item), [
                'courier' => 'SiCepat',
                'tracking_number' => 'SC123456789',
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Seller}
     */
    private function sellerUser(): array
    {
        Role::findOrCreate('seller');

        $user = User::factory()->create();
        $user->assignRole('seller');

        return [$user, Seller::factory()->create(['user_id' => $user->id])];
    }

    private function createOrder(Product $product, PaymentStatus $paymentStatus): Order
    {
        $orderStatus = $paymentStatus === PaymentStatus::Paid
            ? OrderStatus::Paid
            : OrderStatus::PendingPayment;

        $order = Order::query()->create([
            'invoice' => 'AM-SHIP-'.fake()->unique()->numerify('####'),
            'guest_name' => 'Pembeli Pengiriman',
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => '081234567890',
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'subtotal' => 1000000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => 100000,
            'grand_total' => 1000000,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'shipping_snapshot' => [
                'address' => 'Jl. Seni No. 1',
                'city' => 'Yogyakarta',
                'province' => 'DI Yogyakarta',
                'postal_code' => '55111',
            ],
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
            'status' => $orderStatus,
        ]);

        return $order;
    }
}
