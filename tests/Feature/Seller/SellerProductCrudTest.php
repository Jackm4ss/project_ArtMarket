<?php

namespace Tests\Feature\Seller;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SellerProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_create_update_and_delete_own_product(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($sellerUser)
            ->get(route('seller.products.create'))
            ->assertOk();

        $this->actingAs($sellerUser)
            ->post(route('seller.products.store'), $this->payload([
                'category_id' => $category->id,
                'title' => 'Lukisan Ombak Selatan',
            ]))
            ->assertRedirect();

        $product = Product::query()->where('title', 'Lukisan Ombak Selatan')->firstOrFail();

        $this->assertSame($seller->id, $product->seller_id);
        $this->assertSame(ProductStatus::Published, $product->status);
        $this->assertNotNull($product->published_at);

        $this->actingAs($sellerUser)
            ->patch(route('seller.products.update', $product), $this->payload([
                'category_id' => $category->id,
                'title' => 'Lukisan Ombak Selatan Revisi',
                'price' => 2500000,
            ]))
            ->assertRedirect();

        $this->assertSame('Lukisan Ombak Selatan Revisi', $product->fresh()->title);
        $this->assertSame(2500000.0, (float) $product->fresh()->price);

        $this->actingAs($sellerUser)
            ->delete(route('seller.products.destroy', $product->fresh()))
            ->assertRedirect(route('seller.products.index'));

        $this->assertSoftDeleted($product->fresh());
    }

    public function test_seller_update_does_not_republish_admin_unpublished_product(): void
    {
        [$sellerUser, $seller] = $this->sellerUser();
        $product = Product::factory()->create([
            'seller_id' => $seller->id,
            'status' => ProductStatus::Unpublished,
            'published_at' => null,
        ]);

        $this->actingAs($sellerUser)
            ->patch(route('seller.products.update', $product), $this->payload([
                'title' => 'Judul Diperbaiki Seller',
            ]))
            ->assertRedirect();

        $this->assertSame(ProductStatus::Unpublished, $product->fresh()->status);
        $this->assertNull($product->fresh()->published_at);
    }

    public function test_seller_cannot_manage_other_seller_product(): void
    {
        [$sellerUser] = $this->sellerUser();
        $otherProduct = Product::factory()->create();

        $this->actingAs($sellerUser)
            ->get(route('seller.products.edit', $otherProduct))
            ->assertForbidden();

        $this->actingAs($sellerUser)
            ->patch(route('seller.products.update', $otherProduct), $this->payload())
            ->assertForbidden();

        $this->actingAs($sellerUser)
            ->delete(route('seller.products.destroy', $otherProduct))
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

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'category_id' => null,
            'sku' => fake()->unique()->bothify('SELL-####-??'),
            'title' => 'Produk Seni Seller',
            'excerpt' => 'Ringkasan karya seller.',
            'description' => 'Deskripsi karya seller yang cukup detail untuk katalog.',
            'price' => 1500000,
            'compare_at_price' => null,
            'stock' => 4,
            'product_type' => 'ready',
            'material' => 'Akrilik di atas kanvas',
            'dimensions' => '60 x 80 cm',
            'weight_gram' => 2500,
            'location' => 'Yogyakarta',
            'preorder_days' => null,
            ...$overrides,
        ];
    }
}
