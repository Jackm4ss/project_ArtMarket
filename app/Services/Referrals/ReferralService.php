<?php

namespace App\Services\Referrals;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\Seller;
use App\Models\User;
use App\Services\Notifications\MarketplaceNotificationService;
use App\Services\Wallet\WalletLedgerService;
use App\Support\MarketplaceConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReferralService
{
    public function __construct(
        private readonly WalletLedgerService $walletLedgers,
        private readonly MarketplaceNotificationService $notifications,
    )
    {
    }

    public function codeForSeller(Seller $seller): string
    {
        return 'SELLER-'.$seller->id.'-'.Str::upper(Str::padLeft((string) $seller->user_id, 4, '0'));
    }

    public function referrerForCode(?string $code): ?User
    {
        $normalized = $this->normalizeCode($code);

        if ($normalized === null) {
            return null;
        }

        if (! preg_match('/^SELLER-(\d+)-(\d+)$/', $normalized, $matches)) {
            return null;
        }

        $seller = Seller::query()
            ->with('user')
            ->whereKey((int) $matches[1])
            ->where('user_id', (int) $matches[2])
            ->first();

        return $seller?->user;
    }

    public function assertValidCode(?string $code): ?User
    {
        if (! filled($code)) {
            return null;
        }

        $referrer = $this->referrerForCode($code);

        if (! $referrer) {
            throw ValidationException::withMessages([
                'referral_code' => 'Kode referral seller tidak valid.',
            ]);
        }

        return $referrer;
    }

    public function createPendingForSellerRegistration(User $referredUser, ?string $code): ?Referral
    {
        $normalized = $this->normalizeCode($code);

        if ($normalized === null) {
            return null;
        }

        return DB::transaction(function () use ($referredUser, $normalized): Referral {
            $referrer = $this->assertValidCode($normalized);

            if (! $referrer || $referrer->id === $referredUser->id) {
                throw ValidationException::withMessages([
                    'referral_code' => 'Kode referral tidak bisa digunakan oleh akun yang sama.',
                ]);
            }

            $existing = Referral::query()
                ->where('referred_id', $referredUser->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            return Referral::query()->create([
                'referrer_id' => $referrer->id,
                'referred_id' => $referredUser->id,
                'code' => $this->usageCode($normalized, $referredUser->id),
                'referral_code' => $normalized,
                'status' => ReferralStatus::Pending,
                'reward_amount' => 0,
            ]);
        });
    }

    public function qualify(Referral $referral, ?float $rewardAmount = null): Referral
    {
        return DB::transaction(function () use ($referral, $rewardAmount): Referral {
            /** @var Referral $locked */
            $locked = Referral::query()->whereKey($referral->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === ReferralStatus::Rewarded || $locked->status === ReferralStatus::Qualified) {
                return $locked;
            }

            if ($locked->status === ReferralStatus::Rejected) {
                throw ValidationException::withMessages([
                    'referral' => 'Referral yang sudah rejected tidak bisa di-qualify.',
                ]);
            }

            $locked->update([
                'status' => ReferralStatus::Qualified,
                'reward_amount' => $rewardAmount ?? MarketplaceConfig::referralRewardAmount(),
                'qualified_at' => now(),
            ]);
            $this->notifications->referralQualified($locked->fresh('referrer'));

            return $locked->refresh();
        });
    }

    public function reward(Referral $referral, ?float $rewardAmount = null): Referral
    {
        return DB::transaction(function () use ($referral, $rewardAmount): Referral {
            /** @var Referral $locked */
            $locked = Referral::query()->with('referrer.seller', 'referred')->whereKey($referral->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === ReferralStatus::Rewarded) {
                $this->walletLedgers->recordReferralReward($locked);

                return $locked;
            }

            if ($locked->status === ReferralStatus::Rejected) {
                throw ValidationException::withMessages([
                    'referral' => 'Referral rejected tidak bisa diberi reward.',
                ]);
            }

            $amount = $rewardAmount ?? (float) $locked->reward_amount;
            $amount = $amount > 0 ? $amount : MarketplaceConfig::referralRewardAmount();

            $locked->update([
                'status' => ReferralStatus::Rewarded,
                'reward_amount' => $amount,
                'qualified_at' => $locked->qualified_at ?? now(),
                'rewarded_at' => now(),
            ]);

            $this->walletLedgers->recordReferralReward($locked->refresh());
            $this->notifications->referralRewarded($locked->fresh('referrer'));

            return $locked->refresh();
        });
    }

    public function reject(Referral $referral, ?string $adminNote = null): Referral
    {
        return DB::transaction(function () use ($referral, $adminNote): Referral {
            /** @var Referral $locked */
            $locked = Referral::query()->whereKey($referral->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === ReferralStatus::Rewarded) {
                throw ValidationException::withMessages([
                    'referral' => 'Referral yang sudah rewarded tidak bisa ditolak.',
                ]);
            }

            $locked->update([
                'status' => ReferralStatus::Rejected,
                'admin_note' => $adminNote,
                'rejected_at' => now(),
            ]);
            $this->notifications->referralRejected($locked->fresh('referrer'));

            return $locked->refresh();
        });
    }

    private function normalizeCode(?string $code): ?string
    {
        $normalized = Str::upper(trim((string) $code));

        return $normalized === '' ? null : $normalized;
    }

    private function usageCode(string $referralCode, int $referredUserId): string
    {
        return $referralCode.'-U'.Str::padLeft((string) $referredUserId, 6, '0');
    }
}
