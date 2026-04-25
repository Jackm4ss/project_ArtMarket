<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\Cart\CartManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(CartManager $cart): Response
    {
        return Inertia::render('Public/Cart', [
            'cart' => $cart->summary(),
        ]);
    }

    public function store(AddCartItemRequest $request, CartManager $cart): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));

        $cart->add($product, $request->integer('quantity'));

        return back()->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(UpdateCartItemRequest $request, Product $product, CartManager $cart): RedirectResponse
    {
        $cart->update($product, $request->integer('quantity'));

        return back()->with('status', 'Kuantitas keranjang diperbarui.');
    }

    public function destroy(Product $product, CartManager $cart): RedirectResponse
    {
        $cart->remove($product);

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }

    public function clear(CartManager $cart): RedirectResponse
    {
        $cart->clear();

        return back()->with('status', 'Keranjang dikosongkan.');
    }
}
