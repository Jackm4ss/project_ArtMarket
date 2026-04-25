<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Services\Cart\CartManager;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Product $product, CartManager $cart): Response
    {
        abort_unless($product->status === ProductStatus::Published, 404);

        $product->load([
            'seller:id,store_name,slug,rating_average,rating_count,location',
            'category:id,name,slug',
            'reviews' => fn ($query) => $query
                ->published()
                ->with('user:id,name')
                ->latest('id')
                ->limit(20),
            'media',
        ]);

        $relatedProducts = Product::query()
            ->with(['seller:id,store_name,slug', 'category:id,name,slug', 'media'])
            ->published()
            ->whereKeyNot($product->id)
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->orderByDesc('sold_count')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return Inertia::render('Public/ProductShow', [
            'product' => [
                ...$cart->serializeProduct($product),
                'reviews' => $product->reviews->map(fn ($review): array => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'body' => $review->body,
                    'user' => $review->user ? ['name' => $review->user->name] : null,
                ])->values(),
            ],
            'relatedProducts' => $relatedProducts
                ->map(fn (Product $related): array => $cart->serializeProduct($related))
                ->values(),
        ]);
    }
}
