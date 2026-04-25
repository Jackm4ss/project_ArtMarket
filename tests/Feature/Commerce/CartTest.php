<?php

namespace Tests\Feature\Commerce;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_update_remove_and_clear_cart_items(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Cart')
                ->where('cart.total_items', 2)
                ->where('cart.items.0.product.slug', $product->slug)
            );

        $this->patch(route('cart.items.update', $product), ['quantity' => 3])->assertRedirect();

        $this->get(route('cart.show'))
            ->assertInertia(fn ($page) => $page->where('cart.total_items', 3));

        $this->delete(route('cart.items.destroy', $product))->assertRedirect();

        $this->get(route('cart.show'))
            ->assertInertia(fn ($page) => $page->where('cart.total_items', 0));

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->delete(route('cart.clear'))->assertRedirect();
        $this->get(route('cart.show'))->assertInertia(fn ($page) => $page->where('cart.total_items', 0));
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $product = Product::factory()->create(['stock' => 1]);

        $this->from(route('products.show', $product))
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertSessionHasErrors('quantity');
    }
}
