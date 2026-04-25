<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Notifications\MarketplaceNotificationService;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderCompletionService
{
    public function __construct(
        private readonly WalletLedgerService $walletLedgers,
        private readonly MarketplaceNotificationService $notifications,
    )
    {
    }

    public function complete(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            /** @var Order $locked */
            $locked = Order::query()
                ->with('items')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === OrderStatus::Completed) {
                return $locked;
            }

            if ($locked->payment_status !== PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order' => 'Order belum dibayar, tidak bisa diselesaikan.',
                ]);
            }

            if (! in_array($locked->status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Status order belum bisa diselesaikan.',
                ]);
            }

            $locked->update([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
            ]);
            $locked->items()->update(['status' => OrderStatus::Completed->value]);

            $this->walletLedgers->releaseEscrow($locked);
            $this->notifications->orderCompleted($locked->fresh(['user', 'items.seller.user']));

            return $locked->fresh(['items']);
        });
    }
}
