<?php

namespace Tests\Feature\Seller;

use App\Enums\WithdrawStatus;
use App\Models\Seller;
use App\Models\User;
use App\Models\WalletLedger;
use App\Models\Withdraw;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerWithdrawPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_view_withdrawals_page_with_own_history(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $ownWithdraw = Withdraw::query()->create([
            'seller_id' => $seller->id,
            'amount' => 250000,
            'fee' => 6500,
            'status' => WithdrawStatus::Pending,
            'bank_name' => 'BCA',
            'bank_account_name' => 'Studio Seller',
            'bank_account_number' => '1234567890',
            'requested_at' => now(),
        ]);
        $otherWithdraw = Withdraw::query()->create([
            'seller_id' => Seller::factory()->create()->id,
            'amount' => 500000,
            'fee' => 6500,
            'status' => WithdrawStatus::Pending,
            'bank_name' => 'Mandiri',
            'bank_account_name' => 'Seller Lain',
            'bank_account_number' => '0987654321',
            'requested_at' => now(),
        ]);

        $this->actingAs($sellerUser)
            ->get(route('seller.withdrawals.index'))
            ->assertOk()
            ->assertSee('Request Withdraw')
            ->assertSee('Rp 250.000')
            ->assertSee($ownWithdraw->bank_account_number)
            ->assertDontSee($otherWithdraw->bank_account_number);
    }

    public function test_seller_withdraw_requires_complete_payout_bank_account(): void
    {
        [$sellerUser, $seller] = $this->sellerUser([
            'bank_name' => null,
            'bank_account_name' => null,
            'bank_account_number' => null,
        ]);

        config()->set('marketplace.withdraw_minimum', 100000);
        config()->set('marketplace.withdraw_fee', 6500);

        WalletLedger::query()->create([
            'seller_id' => $seller->id,
            'type' => 'escrow_available',
            'amount' => 1000000,
            'balance_after' => 1000000,
            'description' => 'Seed available balance',
            'occurred_at' => now(),
        ]);

        $this->actingAs($sellerUser)
            ->post(route('seller.withdrawals.store'), ['amount' => 250000])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('withdraws', 0);
    }

    /**
     * @param array<string, mixed> $sellerOverrides
     * @return array{0: User, 1: Seller}
     */
    private function sellerUser(array $sellerOverrides = []): array
    {
        Role::findOrCreate('seller');

        $user = User::factory()->create();
        $user->assignRole('seller');

        return [$user, Seller::factory()->create([
            'user_id' => $user->id,
            ...$sellerOverrides,
        ])];
    }
}
