<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Notifications\MarketplaceNotificationService;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderResolutionService
{
    public function __construct(
        private readonly WalletLedgerService $walletLedgers,
        private readonly MarketplaceNotificationService $notifications,
    )
    {
    }

    public function cancelUnpaid(Order $order, ?string $note = null, ?string $adminNote = null): Order
    {
        return DB::transaction(function () use ($order, $note, $adminNote): Order {
            $locked = $this->lockOrder($order);

            if ($locked->status === OrderStatus::Cancelled) {
                $locked->items()->update(['status' => OrderStatus::Cancelled->value]);
                $locked->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Failed->value]);
                $this->releaseReservedStock($locked);

                return $locked->fresh(['items', 'payments']);
            }

            if ($locked->payment_status === PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order' => 'Order yang sudah dibayar harus melalui proses refund.',
                ]);
            }

            $locked->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => $locked->payment_status === PaymentStatus::Expired
                    ? PaymentStatus::Expired
                    : PaymentStatus::Failed,
                'cancelled_at' => $locked->cancelled_at ?? now(),
                'customer_note' => $note ?? $locked->customer_note,
                'admin_note' => $adminNote ?? $locked->admin_note,
            ]);

            $locked->items()->update(['status' => OrderStatus::Cancelled->value]);
            $locked->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Failed->value]);
            $this->releaseReservedStock($locked);
            $this->notifications->orderCancelled($locked->fresh(['user', 'items.seller.user']));

            return $locked->fresh(['items', 'payments']);
        });
    }

    public function requestRefund(Order $order, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $note): Order {
            $locked = $this->lockOrder($order);

            if ($locked->status === OrderStatus::Refunded || $locked->status === OrderStatus::RefundRequested) {
                return $locked;
            }

            if ($locked->payment_status !== PaymentStatus::Paid) {
                throw ValidationException::withMessages([
                    'order' => 'Refund hanya bisa diajukan untuk order yang sudah dibayar.',
                ]);
            }

            if (! in_array($locked->status, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Status order belum bisa diajukan refund.',
                ]);
            }

            $locked->update([
                'status_before_refund' => $locked->status->value,
                'status' => OrderStatus::RefundRequested,
                'refund_requested_at' => $locked->refund_requested_at ?? now(),
                'customer_note' => $note ?? $locked->customer_note,
            ]);

            $locked->items()->update(['status' => OrderStatus::RefundRequested->value]);
            $this->notifications->refundRequested($locked->fresh(['user', 'items.seller.user']));

            return $locked->fresh(['items']);
        });
    }

    public function approveRefund(Order $order, ?string $adminNote = null): Order
    {
        return DB::transaction(function () use ($order, $adminNote): Order {
            $locked = $this->lockOrder($order);

            if ($locked->status === OrderStatus::Refunded) {
                return $locked;
            }

            if (! in_array($locked->status, [OrderStatus::RefundRequested, OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Completed], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Order tidak berada dalam status yang bisa direfund.',
                ]);
            }

            if (! in_array($locked->payment_status, [PaymentStatus::Paid, PaymentStatus::Refunded], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Payment belum paid, refund tidak bisa diproses.',
                ]);
            }

            $locked->update([
                'status' => OrderStatus::Refunded,
                'payment_status' => PaymentStatus::Refunded,
                'refunded_at' => $locked->refunded_at ?? now(),
                'admin_note' => $adminNote ?? $locked->admin_note,
            ]);

            $locked->items()->update(['status' => OrderStatus::Refunded->value]);
            $locked->payments()->where('status', PaymentStatus::Paid->value)->update(['status' => PaymentStatus::Refunded->value]);
            $this->releaseReservedStock($locked);
            $this->walletLedgers->recordRefund($locked);
            $this->notifications->refundApproved($locked->fresh(['user', 'items.seller.user']));

            return $locked->fresh(['items', 'payments']);
        });
    }

    public function rejectRefund(Order $order, ?string $adminNote = null): Order
    {
        return DB::transaction(function () use ($order, $adminNote): Order {
            $locked = $this->lockOrder($order);

            if ($locked->status !== OrderStatus::RefundRequested) {
                return $locked;
            }

            $previousStatus = OrderStatus::tryFrom((string) $locked->status_before_refund) ?? OrderStatus::Paid;

            $locked->update([
                'status' => $previousStatus,
                'admin_note' => $adminNote ?? $locked->admin_note,
            ]);

            $locked->items()->update(['status' => $previousStatus->value]);
            $this->notifications->refundRejected($locked->fresh(['user']));

            return $locked->fresh(['items']);
        });
    }

    private function releaseReservedStock(Order $order): void
    {
        if ($order->stock_released_at) {
            return;
        }

        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            if (! $item->product) {
                continue;
            }

            $product = $item->product()
                ->lockForUpdate()
                ->first();

            $product?->increment('stock', $item->quantity);
        }

        $order->forceFill(['stock_released_at' => now()])->save();
    }

    private function lockOrder(Order $order): Order
    {
        return Order::query()
            ->with(['items.product', 'payments'])
            ->whereKey($order->id)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
