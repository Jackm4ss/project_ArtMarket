<?php

namespace Tests\Feature\Seller;

use App\Enums\AdsPlacement;
use App\Enums\AdsStatus;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerAd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerAdTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_request_manual_ad_slot(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $this->actingAs($sellerUser)
            ->get(route('seller.ads.index'))
            ->assertOk()
            ->assertSee('Request Slot Iklan');

        $this->actingAs($sellerUser)
            ->post(route('seller.ads.store'), [
                'product_id' => $product->id,
                'title' => 'Campaign Koleksi Kayu',
                'placement' => AdsPlacement::CatalogTop->value,
                'budget' => 750000,
                'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'ends_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('seller_ads', [
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'title' => 'Campaign Koleksi Kayu',
            'placement' => AdsPlacement::CatalogTop->value,
            'status' => AdsStatus::Pending->value,
        ]);
    }

    public function test_seller_cannot_request_ad_for_another_seller_product(): void
    {
        [$sellerUser] = $this->sellerUser();
        $otherProduct = Product::factory()->create();

        $this->actingAs($sellerUser)
            ->post(route('seller.ads.store'), [
                'product_id' => $otherProduct->id,
                'title' => 'Campaign Tidak Valid',
                'placement' => AdsPlacement::HomepageFeatured->value,
                'budget' => 500000,
            ])
            ->assertSessionHasErrors('product_id');
    }

    public function test_seller_can_delete_own_pending_or_rejected_ad_only(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $pendingAd = SellerAd::query()->create([
            'seller_id' => $seller->id,
            'title' => 'Pending Campaign',
            'placement' => AdsPlacement::SellerSpotlight,
            'status' => AdsStatus::Pending,
            'budget' => 100000,
        ]);
        $activeAd = SellerAd::query()->create([
            'seller_id' => $seller->id,
            'title' => 'Active Campaign',
            'placement' => AdsPlacement::HomepageFeatured,
            'status' => AdsStatus::Active,
            'budget' => 100000,
        ]);

        $this->actingAs($sellerUser)
            ->delete(route('seller.ads.destroy', $pendingAd))
            ->assertRedirect();

        $this->assertSoftDeleted($pendingAd);

        $this->actingAs($sellerUser)
            ->delete(route('seller.ads.destroy', $activeAd))
            ->assertSessionHasErrors('ad');

        $this->assertNotSoftDeleted($activeAd);
    }

    public function test_seller_cannot_delete_another_seller_ad(): void
    {
        [$sellerUser] = $this->sellerUser();
        $otherAd = SellerAd::query()->create([
            'seller_id' => Seller::factory()->create()->id,
            'title' => 'Campaign Seller Lain',
            'placement' => AdsPlacement::CatalogTop,
            'status' => AdsStatus::Pending,
            'budget' => 100000,
        ]);

        $this->actingAs($sellerUser)
            ->delete(route('seller.ads.destroy', $otherAd))
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
