<?php

namespace Tests\Feature\Commerce;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VoucherCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_checkout_applies_voucher_and_records_redemption(): void
    {
        config()->set('services.midtrans.server_key', '');

        $product = Product::factory()->create(['stock' => 4, 'price' => 500000]);
        $voucher = Voucher::factory()->create([
            'code' => 'SAVE100',
            'type' => 'fixed',
            'value' => 100000,
            'usage_limit' => 10,
            'per_user_limit' => 1,
        ]);
        $idempotencyKey = (string) Str::uuid();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->post(route('checkout.store'), $this->checkoutPayload($idempotencyKey, ['voucher_code' => 'save100']))
            ->assertRedirect();

        $order = Order::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();

        $this->assertSame($voucher->id, $order->voucher_id);
        $this->assertSame('100000.00', $order->discount_total);
        $this->assertSame('400000.00', $order->grand_total);
        $this->assertSame(1, $voucher->fresh()->used_count);
        $this->assertDatabaseHas('voucher_redemptions', [
            'voucher_id' => $voucher->id,
            'order_id' => $order->id,
            'guest_email' => 'nadia@example.test',
            'discount_amount' => 100000,
        ]);
        $this->assertSame('400000.00', Payment::query()->where('order_id', $order->id)->firstOrFail()->amount);
    }

    public function test_percent_voucher_respects_max_discount_and_per_user_limit(): void
    {
        config()->set('services.midtrans.server_key', '');

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 4, 'price' => 1000000]);
        $voucher = Voucher::factory()->percent(20, 100000)->create([
            'code' => 'MAX100',
            'per_user_limit' => 1,
        ]);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->actingAs($user)
            ->post(route('checkout.store'), $this->checkoutPayload((string) Str::uuid(), [
                'voucher_code' => 'MAX100',
                'email' => $user->email,
            ]))
            ->assertRedirect();

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('100000.00', $order->discount_total);
        $this->assertSame('900000.00', $order->grand_total);
        $this->assertSame(1, $voucher->fresh()->used_count);

        $this->actingAs($user)->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->actingAs($user)
            ->from(route('checkout.create'))
            ->post(route('checkout.store'), $this->checkoutPayload((string) Str::uuid(), [
                'voucher_code' => 'MAX100',
                'email' => $user->email,
            ]))
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHasErrors('voucher_code');

        $this->assertSame(1, VoucherRedemption::query()->where('voucher_id', $voucher->id)->count());
        $this->assertSame(1, Order::query()->where('user_id', $user->id)->count());
        $this->assertSame(1, $voucher->fresh()->used_count);
    }

    public function test_voucher_global_limit_and_minimum_order_are_enforced_before_order_creation(): void
    {
        config()->set('services.midtrans.server_key', '');

        $product = Product::factory()->create(['stock' => 2, 'price' => 100000]);
        Voucher::factory()->create([
            'code' => 'LIMITED',
            'value' => 50000,
            'minimum_order_amount' => 250000,
            'usage_limit' => 1,
            'used_count' => 1,
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->from(route('checkout.create'))
            ->post(route('checkout.store'), $this->checkoutPayload((string) Str::uuid(), ['voucher_code' => 'LIMITED']))
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHasErrors('voucher_code');

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, VoucherRedemption::query()->count());
    }

    /**
     * @param array<string, string> $overrides
     * @return array<string, string>
     */
    private function checkoutPayload(string $idempotencyKey, array $overrides = []): array
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
            ...$overrides,
        ];
    }
}
