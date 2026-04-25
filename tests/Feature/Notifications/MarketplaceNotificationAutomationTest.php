<?php

namespace Tests\Feature\Notifications;

use App\Enums\AdsPlacement;
use App\Enums\AdsStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReferralStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Referral;
use App\Models\Seller;
use App\Models\SellerAd;
use App\Models\User;
use App\Models\WalletLedger;
use App\Models\Withdraw;
use App\Services\Ads\SellerAdModerationService;
use App\Services\Chat\ChatService;
use App\Services\Orders\OrderResolutionService;
use App\Services\Referrals\ReferralService;
use App\Services\Wallet\WalletLedgerService;
use App\Services\Withdraws\WithdrawService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MarketplaceNotificationAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_webhook_notifies_buyer_seller_and_admin_once(): void
    {
        config()->set('services.midtrans.server_key', 'secret-key');

        $admin = $this->adminUser();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000000]);
        $sellerUser = $product->seller->user;
        $order = $this->createOrder($buyer, $product, OrderStatus::PendingPayment, PaymentStatus::Pending);
        $order->payments()->create([
            'invoice' => $order->invoice,
            'gateway' => 'midtrans',
            'gateway_reference' => 'trx-paid-001',
            'status' => PaymentStatus::Pending,
            'amount' => 1000000,
            'currency' => 'IDR',
            'idempotency_key' => 'payment-paid-001',
        ]);

        $payload = $this->signedPayload($order->invoice, 'trx-paid-001', 'settlement', '1000000.00');

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

        $this->assertNotificationTypeCount($buyer, 'payment.paid', 1);
        $this->assertNotificationTypeCount($sellerUser, 'order.paid', 1);
        $this->assertNotificationTypeCount($admin, 'payment.paid', 1);
    }

    public function test_order_completion_and_withdraw_lifecycle_emit_notifications_once(): void
    {
        $admin = $this->adminUser();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000000]);
        $seller = $product->seller;
        $sellerUser = $seller->user;
        $order = $this->createOrder($buyer, $product, OrderStatus::Shipped, PaymentStatus::Paid);

        app(WalletLedgerService::class)->recordEscrowPending($order);

        $this->actingAs($buyer)
            ->patch(route('user.orders.complete', $order))
            ->assertRedirect();
        $this->actingAs($buyer)
            ->patch(route('user.orders.complete', $order))
            ->assertRedirect();

        $this->assertNotificationTypeCount($buyer, 'order.completed', 1);
        $this->assertNotificationTypeCount($sellerUser, 'order.completed', 1);

        app(WithdrawService::class)->request($seller, 500000);
        $withdraw = Withdraw::query()->firstOrFail();
        app(WithdrawService::class)->approve($withdraw);
        app(WithdrawService::class)->markPaid($withdraw->fresh());
        app(WithdrawService::class)->markPaid($withdraw->fresh());

        $this->assertNotificationTypeCount($sellerUser, 'withdraw.requested', 1);
        $this->assertNotificationTypeCount($sellerUser, 'withdraw.approved', 1);
        $this->assertNotificationTypeCount($sellerUser, 'withdraw.paid', 1);
        $this->assertNotificationTypeCount($admin, 'withdraw.requested', 1);
    }

    public function test_chat_message_notifies_counterpart_only(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $sellerUser = $product->seller->user;
        $conversation = app(ChatService::class)->startProductConversation($buyer, $product);

        app(ChatService::class)->sendMessage($buyer, $conversation, 'Apakah karya ini masih tersedia?');

        $this->assertNotificationTypeCount($sellerUser, 'chat.message', 1);
        $this->assertNotificationTypeCount($buyer, 'chat.message', 0);
    }

    public function test_shipment_update_notifies_buyer(): void
    {
        Role::findOrCreate('seller');

        $buyer = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000000]);
        $sellerUser = $product->seller->user;
        $sellerUser->assignRole('seller');
        $order = $this->createOrder($buyer, $product, OrderStatus::Paid, PaymentStatus::Paid);
        $item = $order->items()->firstOrFail();

        $this->actingAs($sellerUser)
            ->patch(route('seller.orders.shipment.update', $item), [
                'courier' => 'JNE',
                'tracking_number' => 'JNE123456789',
            ])
            ->assertRedirect();

        $this->assertNotificationTypeCount($buyer, 'order.shipped', 1);
    }

    public function test_refund_request_and_resolution_emit_notifications(): void
    {
        $admin = $this->adminUser();
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['price' => 1000000]);
        $sellerUser = $product->seller->user;
        $order = $this->createOrder($buyer, $product, OrderStatus::Shipped, PaymentStatus::Paid);

        app(OrderResolutionService::class)->requestRefund($order, 'Karya rusak saat tiba.');
        app(OrderResolutionService::class)->rejectRefund($order->fresh(), 'Bukti belum cukup.');

        $this->assertNotificationTypeCount($admin, 'refund.requested', 1);
        $this->assertNotificationTypeCount($sellerUser, 'refund.requested', 1);
        $this->assertNotificationTypeCount($buyer, 'refund.rejected', 1);

        $approvedOrder = $this->createOrder($buyer, $product, OrderStatus::Completed, PaymentStatus::Paid);
        app(WalletLedgerService::class)->recordEscrowPending($approvedOrder);
        app(WalletLedgerService::class)->releaseEscrow($approvedOrder);

        app(OrderResolutionService::class)->requestRefund($approvedOrder, 'Pengajuan baru.');
        app(OrderResolutionService::class)->approveRefund($approvedOrder->fresh(), 'Disetujui admin.');
        app(OrderResolutionService::class)->approveRefund($approvedOrder->fresh(), 'Disetujui admin.');

        $this->assertNotificationTypeCount($buyer, 'refund.approved', 1);
        $this->assertNotificationTypeCount($sellerUser, 'refund.approved', 1);
    }

    public function test_review_submission_notifies_seller(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create(['price' => 750000]);
        $order = $this->createOrder($buyer, $product, OrderStatus::Completed, PaymentStatus::Paid);
        $item = $order->items()->firstOrFail();

        $this->actingAs($buyer)
            ->post(route('user.orders.items.review.store', [$order, $item]), [
                'rating' => 5,
                'title' => 'Karya sampai aman',
                'body' => 'Packaging rapi dan karya sesuai deskripsi.',
            ])
            ->assertRedirect();

        $this->assertNotificationTypeCount($product->seller->user, 'review.created', 1);
    }

    public function test_ads_and_referral_lifecycle_emit_notifications(): void
    {
        $admin = $this->adminUser();
        $seller = Seller::factory()->create();
        $product = Product::factory()->create(['seller_id' => $seller->id]);
        $ad = SellerAd::query()->create([
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'title' => 'Featured Koleksi Kayu',
            'placement' => AdsPlacement::CatalogTop,
            'status' => AdsStatus::Pending,
            'budget' => 750000,
        ]);

        app(\App\Services\Notifications\MarketplaceNotificationService::class)->sellerAdRequested($ad);
        app(SellerAdModerationService::class)->activate($ad);

        $this->assertNotificationTypeCount($admin, 'ad.requested', 1);
        $this->assertNotificationTypeCount($seller->user, 'ad.active', 1);

        $referrer = $seller->user;
        $referral = Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_id' => User::factory()->create()->id,
            'code' => 'SELLER-'.$seller->id.'-U000001',
            'referral_code' => app(ReferralService::class)->codeForSeller($seller),
            'status' => ReferralStatus::Pending,
            'reward_amount' => 0,
        ]);

        $qualified = app(ReferralService::class)->qualify($referral, 75000);
        app(ReferralService::class)->reward($qualified);
        app(ReferralService::class)->reward($qualified->fresh());

        $this->assertNotificationTypeCount($referrer, 'referral.qualified', 1);
        $this->assertNotificationTypeCount($referrer, 'referral.rewarded', 1);
    }

    private function adminUser(): User
    {
        Role::findOrCreate('admin');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    private function createOrder(User $buyer, Product $product, OrderStatus $status, PaymentStatus $paymentStatus): Order
    {
        $subtotal = (float) $product->price;
        $commission = round($subtotal * 0.10, 2);

        $order = Order::query()->create([
            'user_id' => $buyer->id,
            'invoice' => 'AM-NOTIF-'.fake()->unique()->numerify('####'),
            'guest_name' => $buyer->name,
            'guest_email' => $buyer->email,
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
            'shipping_snapshot' => ['name' => $buyer->name],
            'completed_at' => $status === OrderStatus::Completed ? now() : null,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => 1,
            'unit_price' => $subtotal,
            'subtotal' => $subtotal,
            'commission_amount' => $commission,
            'status' => $status,
        ]);

        return $order->fresh('items');
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

    private function assertNotificationTypeCount(User $user, string $type, int $expected): void
    {
        $actual = $user->notifications()
            ->get()
            ->filter(fn ($notification): bool => ($notification->data['type'] ?? null) === $type)
            ->count();

        $this->assertSame($expected, $actual, "Expected {$expected} notification(s) of type [{$type}] for {$user->email}.");
    }
}
