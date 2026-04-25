<?php

namespace Tests\Feature\Commerce;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductReviewStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_review_completed_order_item_once_and_rating_aggregates_refresh(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'rating_average' => 0,
            'rating_count' => 0,
        ]);
        $product->seller->update([
            'rating_average' => 0,
            'rating_count' => 0,
        ]);
        [$order, $item] = $this->createOrderWithItem($user, $product, OrderStatus::Completed, PaymentStatus::Paid);

        $this->actingAs($user)
            ->post(route('user.orders.items.review.store', [$order, $item]), [
                'rating' => 5,
                'title' => 'Karya sampai aman',
                'body' => 'Packaging rapi dan karya sesuai deskripsi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('product_reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => $item->id,
            'rating' => 5,
            'status' => ProductReviewStatus::Published->value,
        ]);
        $this->assertSame('5.00', $product->fresh()->rating_average);
        $this->assertSame(1, $product->fresh()->rating_count);
        $this->assertSame('5.00', $product->seller->fresh()->rating_average);
        $this->assertSame(1, $product->seller->fresh()->rating_count);

        $this->actingAs($user)
            ->from(route('user.orders.show', $order))
            ->post(route('user.orders.items.review.store', [$order, $item]), [
                'rating' => 4,
                'title' => 'Review kedua',
            ])
            ->assertRedirect(route('user.orders.show', $order))
            ->assertSessionHasErrors('order_item');

        $this->assertSame(1, ProductReview::query()->where('order_item_id', $item->id)->count());
    }

    public function test_user_cannot_review_unfinished_or_other_users_order_item(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $product = Product::factory()->create();
        [$order, $item] = $this->createOrderWithItem($owner, $product, OrderStatus::Paid, PaymentStatus::Paid);

        $this->actingAs($owner)
            ->from(route('user.orders.show', $order))
            ->post(route('user.orders.items.review.store', [$order, $item]), ['rating' => 5])
            ->assertRedirect(route('user.orders.show', $order))
            ->assertSessionHasErrors('order_item');

        $completedOrder = $this->createOrderWithItem($owner, $product, OrderStatus::Completed, PaymentStatus::Paid)[0];
        $completedItem = $completedOrder->items()->firstOrFail();

        $this->actingAs($intruder)
            ->post(route('user.orders.items.review.store', [$completedOrder, $completedItem]), ['rating' => 5])
            ->assertNotFound();
    }

    public function test_hidden_reviews_are_excluded_from_product_detail_and_aggregates(): void
    {
        $product = Product::factory()->create([
            'rating_average' => 0,
            'rating_count' => 0,
        ]);
        $visibleReview = ProductReview::factory()->create([
            'product_id' => $product->id,
            'rating' => 4,
            'status' => ProductReviewStatus::Published,
        ]);
        $hiddenReview = ProductReview::factory()->create([
            'product_id' => $product->id,
            'rating' => 1,
            'status' => ProductReviewStatus::Hidden,
        ]);

        $this->assertSame('4.00', $product->fresh()->rating_average);
        $this->assertSame(1, $product->fresh()->rating_count);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/ProductShow')
                ->has('product.reviews', 1)
                ->where('product.reviews.0.id', $visibleReview->id)
            );

        $visibleReview->update(['status' => ProductReviewStatus::Hidden]);

        $this->assertSame('0.00', $product->fresh()->rating_average);
        $this->assertSame(0, $product->fresh()->rating_count);
        $this->assertDatabaseHas('product_reviews', ['id' => $hiddenReview->id]);
    }

    /**
     * @return array{0: Order, 1: OrderItem}
     */
    private function createOrderWithItem(User $user, Product $product, OrderStatus $status, PaymentStatus $paymentStatus): array
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'invoice' => 'AM-REV-'.fake()->unique()->numerify('####'),
            'guest_name' => $user->name,
            'guest_email' => $user->email,
            'guest_phone' => '081234567890',
            'status' => $status,
            'payment_status' => $paymentStatus,
            'subtotal' => 750000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'commission_total' => 75000,
            'grand_total' => 750000,
            'currency' => 'IDR',
            'idempotency_key' => fake()->uuid(),
            'shipping_snapshot' => ['name' => $user->name],
            'completed_at' => $status === OrderStatus::Completed ? now() : null,
        ]);

        $item = $order->items()->create([
            'seller_id' => $product->seller_id,
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_snapshot' => ['title' => $product->title],
            'quantity' => 1,
            'unit_price' => 750000,
            'subtotal' => 750000,
            'commission_amount' => 75000,
            'status' => $status,
        ]);

        return [$order, $item];
    }
}
