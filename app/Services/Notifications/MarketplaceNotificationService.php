<?php

namespace App\Services\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\Referral;
use App\Models\Seller;
use App\Models\SellerAd;
use App\Models\User;
use App\Models\Withdraw;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Collection;

class MarketplaceNotificationService
{
    public function orderPaid(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller.user']);

        $this->notify($order->user, 'payment.paid', 'Pembayaran berhasil', "Invoice {$order->invoice} sudah dibayar dan masuk ke proses seller.", $this->userOrderUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);

        $this->sellerRecipientsForOrder($order)->each(function (Seller $seller) use ($order): void {
            $this->notifySeller($seller, 'order.paid', 'Order baru sudah dibayar', "Invoice {$order->invoice} siap diproses untuk toko Anda.", $this->sellerOrdersUrl(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice,
            ]);
        });

        $this->notifyAdmins('payment.paid', 'Pembayaran baru masuk', "Invoice {$order->invoice} sudah paid.", $this->adminOrdersUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);
    }

    public function orderCancelled(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller.user']);

        $this->notify($order->user, 'order.cancelled', 'Order dibatalkan', "Invoice {$order->invoice} dibatalkan dan stok dikembalikan bila tersedia.", $this->userOrderUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);

        $this->sellerRecipientsForOrder($order)->each(function (Seller $seller) use ($order): void {
            $this->notifySeller($seller, 'order.cancelled', 'Order dibatalkan', "Invoice {$order->invoice} dibatalkan.", $this->sellerOrdersUrl(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice,
            ]);
        });
    }

    public function orderCompleted(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller.user']);

        $this->notify($order->user, 'order.completed', 'Order selesai', "Invoice {$order->invoice} selesai. Terima kasih sudah berbelanja di Art Market.", $this->userOrderUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);

        $this->sellerRecipientsForOrder($order)->each(function (Seller $seller) use ($order): void {
            $this->notifySeller($seller, 'order.completed', 'Dana escrow dirilis', "Invoice {$order->invoice} selesai dan saldo tersedia sudah diperbarui.", $this->sellerWalletUrl(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice,
            ]);
        });
    }

    public function orderShipmentUpdated(OrderItem $item): void
    {
        $item->loadMissing(['order.user', 'seller.user']);
        $order = $item->order;

        if (! $order) {
            return;
        }

        $tracking = $item->tracking_number ? " Resi: {$item->tracking_number}." : '';

        $this->notify($order->user, 'order.shipped', 'Pesanan dikirim', "Item {$item->product_title} pada invoice {$order->invoice} sudah dikirim.{$tracking}", $this->userOrderUrl($order), [
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'invoice' => $order->invoice,
            'courier' => $item->courier,
            'tracking_number' => $item->tracking_number,
        ]);
    }

    public function refundRequested(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller.user']);

        $this->notifyAdmins('refund.requested', 'Refund diajukan', "Buyer mengajukan refund untuk invoice {$order->invoice}.", $this->adminOrdersUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);

        $this->sellerRecipientsForOrder($order)->each(function (Seller $seller) use ($order): void {
            $this->notifySeller($seller, 'refund.requested', 'Refund diajukan', "Buyer mengajukan refund untuk invoice {$order->invoice}.", $this->sellerOrdersUrl(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice,
            ]);
        });
    }

    public function refundApproved(Order $order): void
    {
        $order->loadMissing(['user', 'items.seller.user']);

        $this->notify($order->user, 'refund.approved', 'Refund disetujui', "Refund untuk invoice {$order->invoice} sudah diproses admin.", $this->userOrderUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);

        $this->sellerRecipientsForOrder($order)->each(function (Seller $seller) use ($order): void {
            $this->notifySeller($seller, 'refund.approved', 'Refund diproses', "Refund invoice {$order->invoice} sudah diproses admin.", $this->sellerWalletUrl(), [
                'order_id' => $order->id,
                'invoice' => $order->invoice,
            ]);
        });
    }

    public function refundRejected(Order $order): void
    {
        $order->loadMissing('user');

        $this->notify($order->user, 'refund.rejected', 'Refund ditolak', "Pengajuan refund invoice {$order->invoice} ditolak admin.", $this->userOrderUrl($order), [
            'order_id' => $order->id,
            'invoice' => $order->invoice,
        ]);
    }

    public function withdrawRequested(Withdraw $withdraw): void
    {
        $withdraw->loadMissing('seller.user');

        $this->notifySeller($withdraw->seller, 'withdraw.requested', 'Withdraw diajukan', "Pengajuan withdraw Rp {$this->money($withdraw->amount)} menunggu review admin.", $this->sellerWithdrawalsUrl(), [
            'withdraw_id' => $withdraw->id,
            'amount' => (float) $withdraw->amount,
        ]);

        $this->notifyAdmins('withdraw.requested', 'Withdraw baru', "{$withdraw->seller?->store_name} mengajukan withdraw Rp {$this->money($withdraw->amount)}.", $this->adminWithdrawUrl($withdraw), [
            'withdraw_id' => $withdraw->id,
            'seller_id' => $withdraw->seller_id,
        ]);
    }

    public function withdrawApproved(Withdraw $withdraw): void
    {
        $withdraw->loadMissing('seller.user');

        $this->notifySeller($withdraw->seller, 'withdraw.approved', 'Withdraw disetujui', "Withdraw Rp {$this->money($withdraw->amount)} sudah disetujui dan menunggu pembayaran.", $this->sellerWithdrawalsUrl(), [
            'withdraw_id' => $withdraw->id,
        ]);
    }

    public function withdrawRejected(Withdraw $withdraw): void
    {
        $withdraw->loadMissing('seller.user');

        $this->notifySeller($withdraw->seller, 'withdraw.rejected', 'Withdraw ditolak', "Withdraw Rp {$this->money($withdraw->amount)} ditolak. Saldo reservasi dikembalikan.", $this->sellerWithdrawalsUrl(), [
            'withdraw_id' => $withdraw->id,
        ]);
    }

    public function withdrawPaid(Withdraw $withdraw): void
    {
        $withdraw->loadMissing('seller.user');

        $this->notifySeller($withdraw->seller, 'withdraw.paid', 'Withdraw dibayar', "Withdraw Rp {$this->money($withdraw->amount)} sudah ditandai paid.", $this->sellerWithdrawalsUrl(), [
            'withdraw_id' => $withdraw->id,
        ]);
    }

    public function chatMessageSent(Message $message): void
    {
        $message->loadMissing(['sender', 'conversation.participants.user', 'conversation.seller']);
        $conversation = $message->conversation;

        if (! $conversation) {
            return;
        }

        $conversation->participants
            ->filter(fn ($participant): bool => $participant->user_id !== $message->sender_id && $participant->user !== null)
            ->each(function ($participant) use ($message, $conversation): void {
                $isSeller = $participant->role === 'seller';
                $url = $isSeller ? $this->sellerChatUrl($conversation) : $this->userChatUrl($conversation);
                $senderName = $message->sender?->name ?? 'Pengguna';

                $this->notify($participant->user, 'chat.message', 'Pesan chat baru', "{$senderName} mengirim pesan baru.", $url, [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                ]);
            });
    }

    public function productReviewCreated(ProductReview $review): void
    {
        $review->loadMissing(['product.seller.user', 'user']);
        $product = $review->product;

        if (! $product) {
            return;
        }

        $this->notifySeller($product->seller, 'review.created', 'Ulasan baru diterima', "{$review->user?->name} memberi rating {$review->rating} untuk {$product->title}.", $this->sellerOrdersUrl(), [
            'review_id' => $review->id,
            'product_id' => $product->id,
            'rating' => $review->rating,
        ]);
    }

    public function sellerAdRequested(SellerAd $ad): void
    {
        $ad->loadMissing('seller');

        $this->notifyAdmins('ad.requested', 'Request iklan baru', "{$ad->seller?->store_name} mengajukan slot iklan {$ad->title}.", $this->adminSellerAdUrl($ad), [
            'seller_ad_id' => $ad->id,
            'seller_id' => $ad->seller_id,
        ]);
    }

    public function sellerAdActivated(SellerAd $ad): void
    {
        $ad->loadMissing('seller.user');

        $this->notifySeller($ad->seller, 'ad.active', 'Iklan aktif', "Slot iklan {$ad->title} sudah aktif.", $this->sellerAdsUrl(), [
            'seller_ad_id' => $ad->id,
        ]);
    }

    public function sellerAdRejected(SellerAd $ad): void
    {
        $ad->loadMissing('seller.user');

        $this->notifySeller($ad->seller, 'ad.rejected', 'Iklan ditolak', "Request iklan {$ad->title} ditolak admin.", $this->sellerAdsUrl(), [
            'seller_ad_id' => $ad->id,
        ]);
    }

    public function sellerAdExpired(SellerAd $ad): void
    {
        $ad->loadMissing('seller.user');

        $this->notifySeller($ad->seller, 'ad.expired', 'Iklan selesai', "Slot iklan {$ad->title} sudah selesai.", $this->sellerAdsUrl(), [
            'seller_ad_id' => $ad->id,
        ]);
    }

    public function referralQualified(Referral $referral): void
    {
        $referral->loadMissing('referrer');

        $this->notify($referral->referrer, 'referral.qualified', 'Referral memenuhi syarat', 'Referral seller Anda sudah memenuhi syarat reward.', $this->sellerReferralsUrl(), [
            'referral_id' => $referral->id,
        ]);
    }

    public function referralRewarded(Referral $referral): void
    {
        $referral->loadMissing('referrer');

        $this->notify($referral->referrer, 'referral.rewarded', 'Reward referral dicatat', "Reward referral Rp {$this->money($referral->reward_amount)} masuk ke wallet.", $this->sellerWalletUrl(), [
            'referral_id' => $referral->id,
            'amount' => (float) $referral->reward_amount,
        ]);
    }

    public function referralRejected(Referral $referral): void
    {
        $referral->loadMissing('referrer');

        $this->notify($referral->referrer, 'referral.rejected', 'Referral ditolak', 'Referral seller tidak memenuhi syarat reward.', $this->sellerReferralsUrl(), [
            'referral_id' => $referral->id,
        ]);
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function notify(?User $user, string $type, string $title, string $body, ?string $url = null, array $meta = []): void
    {
        if (! $user) {
            return;
        }

        $payload = array_filter([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'action_url' => $url,
            ...$meta,
        ], fn ($value): bool => $value !== null && $value !== '');

        $user->notify(new MarketplaceNotification($payload));
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function notifySeller(?Seller $seller, string $type, string $title, string $body, ?string $url = null, array $meta = []): void
    {
        $seller?->loadMissing('user');

        $this->notify($seller?->user, $type, $title, $body, $url, [
            'seller_id' => $seller?->id,
            ...$meta,
        ]);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function notifyAdmins(string $type, string $title, string $body, ?string $url = null, array $meta = []): void
    {
        User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->get()
            ->each(fn (User $admin): mixed => $this->notify($admin, $type, $title, $body, $url, $meta));
    }

    /**
     * @return Collection<int, Seller>
     */
    private function sellerRecipientsForOrder(Order $order): Collection
    {
        $order->loadMissing('items.seller.user');

        return $order->items
            ->pluck('seller')
            ->filter()
            ->unique('id')
            ->values();
    }

    private function userOrderUrl(Order $order): ?string
    {
        return $order->user_id ? route('user.orders.show', $order, absolute: false) : null;
    }

    private function sellerOrdersUrl(): string
    {
        return route('seller.orders.index', absolute: false);
    }

    private function sellerWalletUrl(): string
    {
        return route('seller.wallet.index', absolute: false);
    }

    private function sellerWithdrawalsUrl(): string
    {
        return route('seller.withdrawals.index', absolute: false);
    }

    private function sellerAdsUrl(): string
    {
        return route('seller.ads.index', absolute: false);
    }

    private function sellerReferralsUrl(): string
    {
        return route('seller.referrals.index', absolute: false);
    }

    private function sellerChatUrl(Conversation $conversation): string
    {
        return route('seller.chats.show', $conversation, absolute: false);
    }

    private function userChatUrl(Conversation $conversation): string
    {
        return route('user.chats.show', $conversation, absolute: false);
    }

    private function adminOrdersUrl(Order $order): string
    {
        return route('filament.admin.resources.orders.edit', ['record' => $order->getKey()], absolute: false);
    }

    private function adminWithdrawUrl(Withdraw $withdraw): string
    {
        return route('filament.admin.resources.withdraws.edit', ['record' => $withdraw->getKey()], absolute: false);
    }

    private function adminSellerAdUrl(SellerAd $ad): string
    {
        return route('filament.admin.resources.seller-ads.edit', ['record' => $ad->getKey()], absolute: false);
    }

    private function money(float|string $amount): string
    {
        return number_format((float) $amount, 0, ',', '.');
    }
}
