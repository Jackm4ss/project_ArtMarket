<?php

namespace Tests\Feature\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class UserAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_requires_authentication(): void
    {
        $this->get(route('user.dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_own_orders_and_order_detail(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderFor($user);

        $this->actingAs($user)
            ->get(route('user.orders.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('User/Orders')
                ->where('orders.data.0.invoice', $order->invoice)
            );

        $this->actingAs($user)
            ->get(route('user.orders.show', $order))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('User/OrderShow')
                ->where('order.invoice', $order->invoice)
            );
    }

    public function test_user_cannot_view_another_users_order_detail(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->createOrderFor($owner);

        $this->actingAs($intruder)
            ->get(route('user.orders.show', $order))
            ->assertNotFound();
    }

    public function test_user_can_manage_addresses(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('user.addresses.store'), $this->addressPayload(['label' => 'Rumah']))
            ->assertRedirect();

        $address = Address::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertTrue($address->is_default);

        $this->actingAs($user)
            ->patch(route('user.addresses.update', $address), $this->addressPayload(['label' => 'Kantor', 'is_default' => true]))
            ->assertRedirect();

        $this->assertSame('Kantor', $address->fresh()->label);

        $this->actingAs($user)
            ->delete(route('user.addresses.destroy', $address))
            ->assertRedirect();

        $this->assertSoftDeleted($address);
    }

    public function test_user_can_add_and_remove_wishlist_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('user.wishlist.store', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)
            ->get(route('user.wishlist.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('User/Wishlist')
                ->where('wishlist.data.0.product.slug', $product->slug)
            );

        $this->actingAs($user)
            ->delete(route('user.wishlist.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_user_can_mark_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new UserAreaTestNotification());

        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->get(route('user.notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('User/Notifications')
                ->where('notifications.data.0.id', $notification->id)
            );

        $this->actingAs($user)
            ->patch(route('user.notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    private function createOrderFor(User $user): Order
    {
        $product = Product::factory()->create(['price' => 750000]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'invoice' => 'AM-USER-'.fake()->unique()->numerify('####'),
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'guest_phone' => '081234567890',
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'subtotal' => 750000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => 75000,
            'grand_total' => 750000,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'shipping_snapshot' => ['name' => $user->name, 'address' => 'Jl. Seni No. 1'],
        ]);

        $order->items()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => 1,
            'unit_price' => 750000,
            'subtotal' => 750000,
            'commission_amount' => 75000,
            'status' => OrderStatus::Paid,
        ]);

        return $order;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function addressPayload(array $overrides = []): array
    {
        return [
            'label' => 'Rumah',
            'recipient_name' => 'Nadia Kusuma',
            'phone' => '081234567890',
            'province' => 'DI Yogyakarta',
            'city' => 'Yogyakarta',
            'district' => 'Gondokusuman',
            'postal_code' => '55111',
            'address_line' => 'Jl. Seni No. 12',
            'is_default' => true,
            ...$overrides,
        ];
    }
}

class UserAreaTestNotification extends Notification
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order diperbarui',
            'body' => 'Status order Anda berubah.',
        ];
    }
}
