<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\Cart\CartManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserWishlistController extends Controller
{
    public function index(Request $request, CartManager $cart): Response
    {
        $items = Wishlist::query()
            ->with(['product.seller:id,store_name,slug,location,rating_average,rating_count', 'product.category:id,name,slug', 'product.media'])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('User/Wishlist', [
            'wishlist' => $items->through(fn (Wishlist $wishlist): array => [
                'id' => $wishlist->id,
                'product' => $wishlist->product ? $cart->serializeProduct($wishlist->product) : null,
                'created_at' => $wishlist->created_at?->toISOString(),
            ]),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return back()->with('status', 'Produk ditambahkan ke wishlist.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('status', 'Produk dihapus dari wishlist.');
    }
}
