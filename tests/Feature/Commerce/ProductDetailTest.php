<?php

namespace Tests\Feature\Commerce;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_product_detail_is_visible(): void
    {
        $product = Product::factory()->create();

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/ProductShow')
                ->where('product.slug', $product->slug)
            );
    }

    public function test_unpublished_product_detail_returns_not_found(): void
    {
        $product = Product::factory()->create([
            'status' => ProductStatus::Unpublished,
        ]);

        $this->get(route('products.show', $product))->assertNotFound();
    }
}
