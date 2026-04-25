<?php

namespace Tests\Feature\Seller;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_seller_onboarding(): void
    {
        $this->get(route('seller.onboarding.create'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_seller_onboarding(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('seller.onboarding.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Seller/Onboarding'));
    }

    public function test_authenticated_user_can_create_free_store(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('seller.onboarding.store'), [
                'store_name' => 'Studio Rupa Baru',
                'bio' => 'Koleksi seni kontemporer dari Bandung.',
                'location' => 'Bandung',
                'phone' => '081234567890',
                'bank_name' => 'BCA',
                'bank_account_name' => 'Studio Rupa Baru',
                'bank_account_number' => '1234567890',
            ])
            ->assertRedirect(route('seller.dashboard'));

        $this->assertTrue($user->fresh()->hasRole('seller'));
        $this->assertDatabaseHas('sellers', [
            'user_id' => $user->id,
            'store_name' => 'Studio Rupa Baru',
            'status' => 'active',
            'location' => 'Bandung',
        ]);
    }

    public function test_existing_seller_is_redirected_to_seller_dashboard(): void
    {
        Role::findOrCreate('seller');

        $user = User::factory()->create();
        $user->assignRole('seller');
        Seller::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('seller.onboarding.create'))
            ->assertRedirect(route('seller.dashboard'));
    }
}
