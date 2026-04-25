<?php

namespace App\Services\Wallet;

use App\Models\Order;
use App\Models\Referral;
use App\Models\WalletLedger;
use App\Models\Withdraw;
use App\Support\MarketplaceConfig;
use Illuminate\Validation\ValidationException;

class WalletLedgerService
{
    public function recordEscrowPending(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items->groupBy('seller_id') as $sellerId => $items) {
            if (WalletLedger::query()->where('order_id', $order->id)->where('seller_id', $sellerId)->where('type', 'escrow_pending')->exists()) {
                continue;
            }

            $gross = $items->sum(fn ($item): float => (float) $item->subtotal);
            $commission = $items->sum(fn ($item): float => (float) $item->commission_amount);
            $net = round($gross - $commission, 2);

            WalletLedger::query()->create([
                'seller_id' => $sellerId,
                'order_id' => $order->id,
                'type' => 'escrow_pending',
                'amount' => $net,
                'balance_after' => $this->availableBalance((int) $sellerId),
                'description' => "Escrow pending untuk order {$order->invoice}",
                'metadata' => [
                    'gross' => $gross,
                    'commission' => $commission,
                    'invoice' => $order->invoice,
                ],
                'occurred_at' => now(),
            ]);

            WalletLedger::query()->create([
                'seller_id' => $sellerId,
                'order_id' => $order->id,
                'type' => 'commission_recorded',
                'amount' => $commission,
                'balance_after' => $this->availableBalance((int) $sellerId),
                'description' => "Komisi platform untuk order {$order->invoice}",
                'metadata' => [
                    'invoice' => $order->invoice,
                    'rate' => MarketplaceConfig::commissionRate(),
                ],
                'occurred_at' => now(),
            ]);
        }
    }

    public function releaseEscrow(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items->groupBy('seller_id') as $sellerId => $items) {
            if (WalletLedger::query()->where('order_id', $order->id)->where('seller_id', $sellerId)->where('type', 'escrow_available')->exists()) {
                continue;
            }

            $gross = $items->sum(fn ($item): float => (float) $item->subtotal);
            $commission = $items->sum(fn ($item): float => (float) $item->commission_amount);
            $net = round($gross - $commission, 2);
            $balanceAfter = $this->availableBalance((int) $sellerId) + $net;

            WalletLedger::query()->create([
                'seller_id' => $sellerId,
                'order_id' => $order->id,
                'type' => 'escrow_available',
                'amount' => $net,
                'balance_after' => $balanceAfter,
                'description' => "Dana tersedia untuk order {$order->invoice}",
                'metadata' => [
                    'invoice' => $order->invoice,
                    'gross' => $gross,
                    'commission' => $commission,
                ],
                'occurred_at' => now(),
            ]);
        }
    }

    public function recordWithdrawRequested(Withdraw $withdraw): void
    {
        if (WalletLedger::query()->where('withdraw_id', $withdraw->id)->where('type', 'withdraw_requested')->exists()) {
            return;
        }

        $reserved = round((float) $withdraw->amount + (float) $withdraw->fee, 2);
        $balanceAfter = $this->availableBalance($withdraw->seller_id) - $reserved;

        WalletLedger::query()->create([
            'seller_id' => $withdraw->seller_id,
            'withdraw_id' => $withdraw->id,
            'type' => 'withdraw_requested',
            'amount' => $reserved,
            'balance_after' => $balanceAfter,
            'description' => "Request withdraw #{$withdraw->id}",
            'metadata' => [
                'amount' => (float) $withdraw->amount,
                'fee' => (float) $withdraw->fee,
                'status' => $withdraw->status->value,
            ],
            'occurred_at' => now(),
        ]);
    }

    public function recordWithdrawRejected(Withdraw $withdraw): void
    {
        if (WalletLedger::query()->where('withdraw_id', $withdraw->id)->where('type', 'withdraw_rejected')->exists()) {
            return;
        }

        $returned = round((float) $withdraw->amount + (float) $withdraw->fee, 2);
        $balanceAfter = $this->availableBalance($withdraw->seller_id) + $returned;

        WalletLedger::query()->create([
            'seller_id' => $withdraw->seller_id,
            'withdraw_id' => $withdraw->id,
            'type' => 'withdraw_rejected',
            'amount' => $returned,
            'balance_after' => $balanceAfter,
            'description' => "Withdraw #{$withdraw->id} ditolak dan saldo dikembalikan",
            'metadata' => [
                'amount' => (float) $withdraw->amount,
                'fee' => (float) $withdraw->fee,
                'status' => $withdraw->status->value,
            ],
            'occurred_at' => now(),
        ]);
    }

    public function recordWithdrawPaid(Withdraw $withdraw): void
    {
        if (WalletLedger::query()->where('withdraw_id', $withdraw->id)->where('type', 'withdraw_paid')->exists()) {
            return;
        }

        WalletLedger::query()->create([
            'seller_id' => $withdraw->seller_id,
            'withdraw_id' => $withdraw->id,
            'type' => 'withdraw_paid',
            'amount' => round((float) $withdraw->amount, 2),
            'balance_after' => $this->availableBalance($withdraw->seller_id),
            'description' => "Withdraw #{$withdraw->id} sudah dibayarkan",
            'metadata' => [
                'amount' => (float) $withdraw->amount,
                'fee' => (float) $withdraw->fee,
                'status' => $withdraw->status->value,
            ],
            'occurred_at' => now(),
        ]);
    }

    public function recordReferralReward(Referral $referral): void
    {
        if (WalletLedger::query()->where('referral_id', $referral->id)->where('type', 'referral_rewarded')->exists()) {
            return;
        }

        $referral->loadMissing('referrer.seller', 'referred');
        $seller = $referral->referrer?->seller;

        if (! $seller) {
            throw ValidationException::withMessages([
                'referral' => 'Referrer belum memiliki profil seller.',
            ]);
        }

        $amount = round((float) $referral->reward_amount, 2);

        WalletLedger::query()->create([
            'seller_id' => $seller->id,
            'referral_id' => $referral->id,
            'type' => 'referral_rewarded',
            'amount' => $amount,
            'balance_after' => $this->availableBalance($seller->id) + $amount,
            'description' => "Reward referral {$referral->referral_code}",
            'metadata' => [
                'referral_id' => $referral->id,
                'referral_code' => $referral->referral_code,
                'referred_id' => $referral->referred_id,
                'referred_email' => $referral->referred?->email,
            ],
            'occurred_at' => now(),
        ]);
    }

    public function recordRefund(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items->groupBy('seller_id') as $sellerId => $items) {
            if (WalletLedger::query()->where('order_id', $order->id)->where('seller_id', $sellerId)->where('type', 'refund_recorded')->exists()) {
                continue;
            }

            $gross = $items->sum(fn ($item): float => (float) $item->subtotal);
            $commission = $items->sum(fn ($item): float => (float) $item->commission_amount);
            $net = round($gross - $commission, 2);
            $escrowWasReleased = WalletLedger::query()
                ->where('order_id', $order->id)
                ->where('seller_id', $sellerId)
                ->where('type', 'escrow_available')
                ->exists();

            if ($escrowWasReleased && ! WalletLedger::query()->where('order_id', $order->id)->where('seller_id', $sellerId)->where('type', 'refund_debited')->exists()) {
                WalletLedger::query()->create([
                    'seller_id' => $sellerId,
                    'order_id' => $order->id,
                    'type' => 'refund_debited',
                    'amount' => $net,
                    'balance_after' => $this->availableBalance((int) $sellerId) - $net,
                    'description' => "Refund order {$order->invoice} dari saldo tersedia seller",
                    'metadata' => [
                        'invoice' => $order->invoice,
                        'gross' => $gross,
                        'commission' => $commission,
                    ],
                    'occurred_at' => now(),
                ]);
            }

            WalletLedger::query()->create([
                'seller_id' => $sellerId,
                'order_id' => $order->id,
                'type' => 'refund_recorded',
                'amount' => $net,
                'balance_after' => $this->availableBalance((int) $sellerId),
                'description' => "Refund tercatat untuk order {$order->invoice}",
                'metadata' => [
                    'invoice' => $order->invoice,
                    'gross' => $gross,
                    'commission' => $commission,
                    'escrow_was_released' => $escrowWasReleased,
                ],
                'occurred_at' => now(),
            ]);
        }
    }

    public function availableBalance(int $sellerId): float
    {
        return (float) WalletLedger::query()
            ->where('seller_id', $sellerId)
            ->whereIn('type', ['escrow_available', 'withdraw_rejected', 'refund_reversed', 'referral_rewarded'])
            ->sum('amount')
            - (float) WalletLedger::query()
                ->where('seller_id', $sellerId)
                ->whereIn('type', ['withdraw_requested', 'refund_debited'])
                ->sum('amount');
    }
}
