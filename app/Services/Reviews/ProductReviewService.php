<?php

namespace App\Services\Reviews;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductReviewStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Services\Notifications\MarketplaceNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReviewService
{
    public function __construct(private readonly MarketplaceNotificationService $notifications)
    {
    }

    /**
     * @param array{rating: int, title?: string|null, body?: string|null} $data
     */
    public function createForOrderItem(User $user, Order $order, OrderItem $orderItem, array $data): ProductReview
    {
        return DB::transaction(function () use ($user, $order, $orderItem, $data): ProductReview {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var OrderItem $lockedItem */
            $lockedItem = OrderItem::query()
                ->with('product')
                ->whereKey($orderItem->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->user_id !== $user->id || $lockedItem->order_id !== $lockedOrder->id) {
                throw ValidationException::withMessages([
                    'order_item' => 'Item order tidak valid.',
                ]);
            }

            if ($lockedOrder->status !== OrderStatus::Completed || $lockedOrder->payment_status !== PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order_item' => 'Ulasan hanya bisa diberikan setelah order selesai.',
                ]);
            }

            if (! $lockedItem->product_id || ! $lockedItem->product) {
                throw ValidationException::withMessages([
                    'order_item' => 'Produk pada order ini sudah tidak tersedia untuk diulas.',
                ]);
            }

            $alreadyReviewed = ProductReview::withTrashed()
                ->where('order_item_id', $lockedItem->id)
                ->exists();

            if ($alreadyReviewed) {
                throw ValidationException::withMessages([
                    'order_item' => 'Item ini sudah pernah diberi ulasan.',
                ]);
            }

            $review = ProductReview::query()->create([
                'user_id' => $user->id,
                'product_id' => $lockedItem->product_id,
                'order_item_id' => $lockedItem->id,
                'rating' => (int) $data['rating'],
                'title' => $data['title'] ?? null,
                'body' => $data['body'] ?? null,
                'status' => ProductReviewStatus::Published,
            ]);

            $this->notifications->productReviewCreated($review->load(['product.seller.user', 'user']));

            return $review;
        });
    }

    public function refreshAggregates(Product|int|null $product): void
    {
        if (! $product) {
            return;
        }

        $product = $product instanceof Product
            ? $product->fresh('seller')
            : Product::query()->with('seller')->find($product);

        if (! $product) {
            return;
        }

        $productAggregate = ProductReview::query()
            ->where('product_id', $product->id)
            ->published()
            ->selectRaw('COUNT(*) as aggregate_count, AVG(rating) as aggregate_average')
            ->first();

        $product->forceFill([
            'rating_count' => (int) ($productAggregate?->aggregate_count ?? 0),
            'rating_average' => round((float) ($productAggregate?->aggregate_average ?? 0), 2),
        ])->saveQuietly();

        if (! $product->seller_id) {
            return;
        }

        $sellerAggregate = ProductReview::query()
            ->published()
            ->whereHas('product', fn (Builder $query): Builder => $query->where('seller_id', $product->seller_id))
            ->selectRaw('COUNT(*) as aggregate_count, AVG(rating) as aggregate_average')
            ->first();

        $product->seller?->forceFill([
            'rating_count' => (int) ($sellerAggregate?->aggregate_count ?? 0),
            'rating_average' => round((float) ($sellerAggregate?->aggregate_average ?? 0), 2),
        ])->saveQuietly();
    }
}
