<?php

namespace App\Services\Withdraws;

use App\Enums\WithdrawStatus;
use App\Models\Seller;
use App\Models\Withdraw;
use App\Services\Notifications\MarketplaceNotificationService;
use App\Services\Wallet\WalletLedgerService;
use App\Support\MarketplaceConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WithdrawService
{
    public function __construct(
        private readonly WalletLedgerService $walletLedgers,
        private readonly MarketplaceNotificationService $notifications,
    )
    {
    }

    public function request(Seller $seller, float $amount): Withdraw
    {
        $fee = MarketplaceConfig::withdrawFee();
        $minimum = MarketplaceConfig::withdrawMinimum();

        if ($amount < $minimum) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal withdraw belum memenuhi minimum.',
            ]);
        }

        if (! $seller->bank_name || ! $seller->bank_account_name || ! $seller->bank_account_number) {
            throw ValidationException::withMessages([
                'amount' => 'Lengkapi rekening payout toko sebelum mengajukan withdraw.',
            ]);
        }

        return DB::transaction(function () use ($seller, $amount, $fee): Withdraw {
            $available = $this->walletLedgers->availableBalance($seller->id);
            $reserved = $amount + $fee;

            if ($available < $reserved) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tersedia tidak mencukupi nominal withdraw dan biaya admin.',
                ]);
            }

            $withdraw = Withdraw::query()->create([
                'seller_id' => $seller->id,
                'amount' => $amount,
                'fee' => $fee,
                'status' => WithdrawStatus::Pending,
                'bank_name' => $seller->bank_name ?? '',
                'bank_account_name' => $seller->bank_account_name ?? '',
                'bank_account_number' => $seller->bank_account_number ?? '',
                'requested_at' => now(),
            ]);

            $this->walletLedgers->recordWithdrawRequested($withdraw);
            $this->notifications->withdrawRequested($withdraw->fresh('seller.user'));

            return $withdraw;
        });
    }

    public function approve(Withdraw $withdraw): Withdraw
    {
        return DB::transaction(function () use ($withdraw): Withdraw {
            /** @var Withdraw $locked */
            $locked = Withdraw::query()->whereKey($withdraw->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== WithdrawStatus::Pending) {
                return $locked;
            }

            $locked->update([
                'status' => WithdrawStatus::Approved,
                'processed_at' => now(),
            ]);
            $this->notifications->withdrawApproved($locked->fresh('seller.user'));

            return $locked;
        });
    }

    public function reject(Withdraw $withdraw, ?string $note = null): Withdraw
    {
        return DB::transaction(function () use ($withdraw, $note): Withdraw {
            /** @var Withdraw $locked */
            $locked = Withdraw::query()->whereKey($withdraw->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === WithdrawStatus::Rejected) {
                return $locked;
            }

            if (! in_array($locked->status, [WithdrawStatus::Pending, WithdrawStatus::Approved], true)) {
                throw ValidationException::withMessages([
                    'withdraw' => 'Withdraw tidak bisa ditolak pada status saat ini.',
                ]);
            }

            $locked->update([
                'status' => WithdrawStatus::Rejected,
                'admin_note' => $note ?? $locked->admin_note,
                'processed_at' => now(),
            ]);

            $this->walletLedgers->recordWithdrawRejected($locked);
            $this->notifications->withdrawRejected($locked->fresh('seller.user'));

            return $locked;
        });
    }

    public function markPaid(Withdraw $withdraw): Withdraw
    {
        return DB::transaction(function () use ($withdraw): Withdraw {
            /** @var Withdraw $locked */
            $locked = Withdraw::query()->whereKey($withdraw->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === WithdrawStatus::Paid) {
                return $locked;
            }

            if ($locked->status !== WithdrawStatus::Approved) {
                throw ValidationException::withMessages([
                    'withdraw' => 'Withdraw harus approved sebelum ditandai paid.',
                ]);
            }

            $locked->update([
                'status' => WithdrawStatus::Paid,
                'processed_at' => now(),
            ]);

            $this->walletLedgers->recordWithdrawPaid($locked);
            $this->notifications->withdrawPaid($locked->fresh('seller.user'));

            return $locked;
        });
    }
}
