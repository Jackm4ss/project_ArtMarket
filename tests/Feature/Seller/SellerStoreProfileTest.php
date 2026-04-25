<?php

namespace Tests\Feature\Seller;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerStoreProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_view_and_update_store_profile(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();

        $this->actingAs($sellerUser)
            ->get(route('seller.store.edit'))
            ->assertOk()
            ->assertSee($seller->store_name);

        $this->actingAs($sellerUser)
            ->patch(route('seller.store.update'), [
                'store_name' => 'Studio Rupa Selatan',
                'bio' => 'Studio seni kontemporer dari Yogyakarta.',
                'location' => 'Yogyakarta',
                'phone' => '081234567890',
                'bank_name' => 'BCA',
                'bank_account_name' => 'Studio Rupa Selatan',
                'bank_account_number' => '1234567890',
            ])
            ->assertRedirect();

        $seller->refresh();

        $this->assertSame('Studio Rupa Selatan', $seller->store_name);
        $this->assertSame('Yogyakarta', $seller->location);
        $this->assertSame('BCA', $seller->bank_name);
        $this->assertSame('1234567890', $seller->bank_account_number);
    }

    public function test_seller_store_name_must_be_unique(): void
    {
        [$sellerUser] = $this->sellerUser();
        Seller::factory()->create(['store_name' => 'Nama Toko Dipakai']);

        $this->actingAs($sellerUser)
            ->patch(route('seller.store.update'), [
                'store_name' => 'Nama Toko Dipakai',
            ])
            ->assertSessionHasErrors('store_name');
    }

    public function test_admin_without_seller_profile_cannot_open_seller_store_page(): void
    {
        Role::findOrCreate('admin');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('seller.store.edit'))
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
