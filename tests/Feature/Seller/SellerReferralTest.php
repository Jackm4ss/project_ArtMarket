<?php

namespace Tests\Feature\Seller;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_view_referral_code_summary_and_own_history(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $referredUser = User::factory()->create(['name' => 'Seller Baru Referral']);
        $otherSellerUser = User::factory()->create();

        Referral::query()->create([
            'referrer_id' => $sellerUser->id,
            'referred_id' => $referredUser->id,
            'code' => 'SELLER-'.$seller->id.'-000'.$sellerUser->id,
            'status' => ReferralStatus::Rewarded,
            'reward_amount' => 50000,
            'rewarded_at' => now(),
        ]);

        Referral::query()->create([
            'referrer_id' => $otherSellerUser->id,
            'referred_id' => User::factory()->create()->id,
            'code' => 'OTHER-CODE',
            'status' => ReferralStatus::Pending,
            'reward_amount' => 0,
        ]);

        $this->actingAs($sellerUser)
            ->get(route('seller.referrals.index'))
            ->assertOk()
            ->assertSee('SELLER-'.$seller->id)
            ->assertSee('Seller Baru Referral')
            ->assertSee('Rp 50.000')
            ->assertDontSee('OTHER-CODE');
    }

    public function test_admin_without_seller_profile_cannot_open_seller_referrals(): void
    {
        Role::findOrCreate('admin');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('seller.referrals.index'))
            ->assertForbidden();
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
