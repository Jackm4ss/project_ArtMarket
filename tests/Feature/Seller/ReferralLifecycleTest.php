<?php

namespace Tests\Feature\Seller;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\Seller;
use App\Models\User;
use App\Models\WalletLedger;
use App\Services\Referrals\ReferralService;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReferralLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_can_be_qualified_and_rewarded_once_to_wallet_ledger(): void
    {
        config()->set('marketplace.referral_reward_amount', 75000);

        [$referrerUser, $seller] = $this->sellerUser();
        $referredUser = User::factory()->create(['email' => 'seller-referred@example.com']);
        $code = app(ReferralService::class)->codeForSeller($seller);

        $referral = Referral::query()->create([
            'referrer_id' => $referrerUser->id,
            'referred_id' => $referredUser->id,
            'code' => $code.'-U000999',
            'referral_code' => $code,
            'status' => ReferralStatus::Pending,
            'reward_amount' => 0,
        ]);

        $service = app(ReferralService::class);
        $qualified = $service->qualify($referral);
        $rewarded = $service->reward($qualified);
        $service->reward($rewarded);

        $this->assertSame(ReferralStatus::Rewarded, $rewarded->fresh()->status);
        $this->assertSame(75000.0, (float) $rewarded->fresh()->reward_amount);
        $this->assertSame(1, WalletLedger::query()->where('referral_id', $referral->id)->where('type', 'referral_rewarded')->count());
        $this->assertSame(75000.0, app(WalletLedgerService::class)->availableBalance($seller->id));
    }

    public function test_rewarded_referral_cannot_be_rejected(): void
    {
        [$referrerUser, $seller] = $this->sellerUser();
        $code = app(ReferralService::class)->codeForSeller($seller);
        $referral = Referral::query()->create([
            'referrer_id' => $referrerUser->id,
            'referred_id' => User::factory()->create()->id,
            'code' => $code.'-U000777',
            'referral_code' => $code,
            'status' => ReferralStatus::Rewarded,
            'reward_amount' => 50000,
            'rewarded_at' => now(),
        ]);

        app(WalletLedgerService::class)->recordReferralReward($referral);

        try {
            app(ReferralService::class)->reject($referral, 'Tidak valid');
            $this->fail('Rewarded referral should not be rejected.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Referral yang sudah rewarded tidak bisa ditolak.',
                $exception->errors()['referral'][0],
            );
        }
    }

    /**
     * @return array{0: User, 1: Seller}
     */
    private function sellerUser(): array
    {
        Role::findOrCreate('seller');

        $user = User::factory()->create();
        $user->assignRole('seller');

        return [$user, Seller::factory()->create(['user_id' => $user->id])];
    }
}
