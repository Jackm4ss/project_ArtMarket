<?php

namespace Tests\Feature\Auth;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\Seller;
use App\Models\User;
use App\Services\Referrals\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_registration_screen_can_be_rendered_with_referral_code(): void
    {
        [, $seller] = $this->sellerUser();
        $code = app(ReferralService::class)->codeForSeller($seller);

        $this->get(route('seller.register', ['ref' => $code]))
            ->assertOk();
    }

    public function test_new_seller_can_register_without_referral(): void
    {
        $this->post(route('seller.register.store'), $this->payload([
            'email' => 'seller-new@example.com',
            'store_name' => 'Studio Seller Baru',
        ]))
            ->assertRedirect(route('seller.dashboard', absolute: false));

        $user = User::query()->where('email', 'seller-new@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('seller'));
        $this->assertDatabaseHas('sellers', [
            'user_id' => $user->id,
            'store_name' => 'Studio Seller Baru',
            'status' => 'active',
        ]);
        $this->assertDatabaseCount('referrals', 0);
    }

    public function test_new_seller_registration_with_valid_referral_creates_pending_referral(): void
    {
        [$referrerUser, $referrerSeller] = $this->sellerUser();
        $code = app(ReferralService::class)->codeForSeller($referrerSeller);

        $this->post(route('seller.register.store'), $this->payload([
            'email' => 'referred-seller@example.com',
            'store_name' => 'Studio Seller Referral',
            'referral_code' => $code,
        ]))
            ->assertRedirect(route('seller.dashboard', absolute: false));

        $referredUser = User::query()->where('email', 'referred-seller@example.com')->firstOrFail();

        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrerUser->id,
            'referred_id' => $referredUser->id,
            'referral_code' => $code,
            'status' => ReferralStatus::Pending->value,
        ]);

        $referral = Referral::query()->firstOrFail();
        $this->assertStringStartsWith($code.'-U', $referral->code);
    }

    public function test_new_seller_registration_rejects_invalid_referral_code(): void
    {
        $this->post(route('seller.register.store'), $this->payload([
            'email' => 'invalid-ref@example.com',
            'store_name' => 'Studio Invalid Referral',
            'referral_code' => 'SELLER-999-9999',
        ]))
            ->assertSessionHasErrors('referral_code');

        $this->assertDatabaseMissing('users', ['email' => 'invalid-ref@example.com']);
        $this->assertDatabaseCount('referrals', 0);
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

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Seller Baru',
            'email' => 'seller@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'store_name' => 'Studio Baru',
            'bio' => 'Studio seni baru.',
            'location' => 'Bandung',
            'phone' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_name' => 'Studio Baru',
            'bank_account_number' => '1234567890',
            'referral_code' => null,
            ...$overrides,
        ];
    }
}
