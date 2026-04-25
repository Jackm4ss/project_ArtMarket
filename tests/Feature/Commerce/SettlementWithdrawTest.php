<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\WithdrawStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\WalletLedger;
use App\Models\Withdraw;
use App\Services\Wallet\WalletLedgerService;
use App\Services\Withdraws\WithdrawService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettlementWithdrawTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_paid_order_and_release_escrow_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000000]);
        $order = $this->createOrder($user, $product, OrderStatus::Shipped);

        app(WalletLedgerService::class)->recordEscrowPending($order);

        $this->actingAs($user)
            ->patch(route('user.orders.complete', $order))
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('user.orders.complete', $order))
            ->assertRedirect();

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame(1, WalletLedger::query()->where('type', 'escrow_available')->where('order_id', $order->id)->count());
    }

    public function test_seller_can_request_withdraw_and_admin_can_mark_paid(): void
    {
        Role::findOrCreate('seller');
        config()->set('marketplace.withdraw_minimum', 100000);
        config()->set('marketplace.withdraw_fee', 6500);

        $sellerUser = User::factory()->create();
        $sellerUser->assignRole('seller');
        $seller = Seller::factory()->create([
            'user_id' => $sellerUser->id,
            'bank_name' => 'BCA',
            'bank_account_name' => 'Nusantara Studio',
            'bank_account_number' => '1234567890',
        ]);

        WalletLedger::query()->create([
            'seller_id' => $seller->id,
            'type' => 'escrow_available',
            'amount' => 1000000,
            'balance_after' => 1000000,
            'description' => 'Seed available balance',
            'occurred_at' => now(),
        ]);

        $this->actingAs($sellerUser)
            ->post(route('seller.withdrawals.store'), ['amount' => 500000])
            ->assertRedirect();

        $withdraw = Withdraw::query()->firstOrFail();
        $this->assertSame(WithdrawStatus::Pending, $withdraw->status);
        $this->assertDatabaseHas('wallet_ledgers', [
            'withdraw_id' => $withdraw->id,
            'type' => 'withdraw_requested',
            'amount' => 506500,
        ]);
        $this->assertSame(493500.0, app(WalletLedgerService::class)->availableBalance($seller->id));

        app(WithdrawService::class)->approve($withdraw);
        app(WithdrawService::class)->markPaid($withdraw->fresh());
        app(WithdrawService::class)->markPaid($withdraw->fresh());

        $this->assertSame(WithdrawStatus::Paid, $withdraw->fresh()->status);
        $this->assertSame(1, WalletLedger::query()->where('withdraw_id', $withdraw->id)->where('type', 'withdraw_paid')->count());
        $this->assertSame(493500.0, app(WalletLedgerService::class)->availableBalance($seller->id));
    }

    public function test_rejected_withdraw_returns_reserved_balance_once(): void
    {
        config()->set('marketplace.withdraw_minimum', 100000);
        config()->set('marketplace.withdraw_fee', 6500);

        $seller = Seller::factory()->create();
        WalletLedger::query()->create([
            'seller_id' => $seller->id,
            'type' => 'escrow_available',
            'amount' => 1000000,
            'balance_after' => 1000000,
            'description' => 'Seed available balance',
            'occurred_at' => now(),
        ]);

        $withdraw = app(WithdrawService::class)->request($seller, 500000);
        app(WithdrawService::class)->reject($withdraw, 'Data rekening salah');
        app(WithdrawService::class)->reject($withdraw->fresh(), 'Data rekening salah');

        $this->assertSame(WithdrawStatus::Rejected, $withdraw->fresh()->status);
        $this->assertSame(1, WalletLedger::query()->where('withdraw_id', $withdraw->id)->where('type', 'withdraw_rejected')->count());
        $this->assertSame(1000000.0, app(WalletLedgerService::class)->availableBalance($seller->id));
    }

    private function createOrder(User $user, Product $product, OrderStatus $status): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'invoice' => 'AM-SETTLE-'.fake()->unique()->numerify('####'),
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'guest_phone' => '081234567890',
            'status' => $status,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 1000000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => 100000,
            'grand_total' => 1000000,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'shipping_snapshot' => ['name' => $user->name],
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
            'status' => $status,
        ]);

        return $order;
    }
}
