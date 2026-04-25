<?php

namespace Tests\Feature\Seller;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_report_summarizes_only_own_paid_order_items(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $ownProduct = Product::factory()->create(['seller_id' => $seller->id, 'title' => 'Laporan Produk Sendiri']);
        $otherProduct = Product::factory()->create(['title' => 'Produk Seller Lain']);

        $this->createOrderItem($ownProduct, PaymentStatus::Paid, OrderStatus::Completed, subtotal: 1000000, commission: 100000);
        $this->createOrderItem($otherProduct, PaymentStatus::Paid, OrderStatus::Completed, subtotal: 700000, commission: 70000);
        $this->createOrderItem($ownProduct, PaymentStatus::Pending, OrderStatus::PendingPayment, subtotal: 300000, commission: 30000);

        $this->actingAs($sellerUser)
            ->get(route('seller.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Produk Sendiri')
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 900.000')
            ->assertSee('Completed')
            ->assertDontSee('Produk Seller Lain');
    }

    public function test_seller_report_date_filter_excludes_outside_period(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $recentProduct = Product::factory()->create(['seller_id' => $seller->id, 'title' => 'Produk Periode Ini']);
        $oldProduct = Product::factory()->create(['seller_id' => $seller->id, 'title' => 'Produk Lama']);

        $this->createOrderItem($recentProduct, PaymentStatus::Paid, OrderStatus::Paid, createdAt: now()->subDays(2));
        $this->createOrderItem($oldProduct, PaymentStatus::Paid, OrderStatus::Paid, createdAt: now()->subDays(60));

        $this->actingAs($sellerUser)
            ->get(route('seller.reports.index', [
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('Produk Periode Ini')
            ->assertDontSee('Produk Lama');
    }

    public function test_seller_report_rejects_invalid_date_range(): void
    {
        [$sellerUser] = $this->sellerUser();

        $this->actingAs($sellerUser)
            ->get(route('seller.reports.index', [
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->subDay()->format('Y-m-d'),
            ]))
            ->assertSessionHasErrors('end_date');
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

    private function createOrderItem(
        Product $product,
        PaymentStatus $paymentStatus,
        OrderStatus $orderStatus,
        float $subtotal = 500000,
        float $commission = 50000,
        ?Carbon $createdAt = null,
    ): Order {
        $createdAt ??= now();

        $order = Order::query()->create([
            'invoice' => 'AM-REPORT-'.fake()->unique()->numerify('####'),
            'guest_name' => 'Pembeli Report',
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => '081234567890',
            'status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => $commission,
            'grand_total' => $subtotal,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'shipping_snapshot' => ['address' => 'Jl. Report No. 1'],
        ]);

        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        $order->items()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => 1,
            'unit_price' => $subtotal,
            'subtotal' => $subtotal,
            'commission_amount' => $commission,
            'status' => $orderStatus,
        ]);

        return $order;
    }
}
