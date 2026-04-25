<?php

namespace App\Services\Cart;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Support\MarketplaceConfig;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartManager
{
    private const SESSION_KEY = 'artmarket.cart.items';

    public function __construct(private readonly SessionStore $session)
    {
    }

    /**
     * @return array<int, int>
     */
    public function rawItems(): array
    {
        return collect($this->session->get(self::SESSION_KEY, []))
            ->mapWithKeys(fn ($quantity, $productId) => [(int) $productId => max(1, (int) $quantity)])
            ->all();
    }

    public function count(): int
    {
        return array_sum($this->rawItems());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(): Collection
    {
        $cartItems = $this->rawItems();

        if ($cartItems === []) {
            return collect();
        }

        $products = Product::query()
            ->with(['seller:id,store_name,slug,location,rating_average,rating_count', 'category:id,name,slug', 'media'])
            ->whereIn('id', array_keys($cartItems))
            ->get()
            ->keyBy('id');

        return collect($cartItems)
            ->map(function (int $quantity, int $productId) use ($products): ?array {
                /** @var Product|null $product */
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $price = (float) $product->price;

                return [
                    'product' => $this->serializeProduct($product),
                    'quantity' => $quantity,
                    'line_total' => $price * $quantity,
                    'stock_state' => $this->stockState($product, $quantity),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $items = $this->items();
        $subtotal = $items->sum(fn (array $item): float => (float) $item['line_total']);

        return [
            'items' => $items->values()->all(),
            'total_items' => $items->sum(fn (array $item): int => (int) $item['quantity']),
            'subtotal' => $subtotal,
            'currency' => MarketplaceConfig::currency(),
            'has_stock_issue' => $items->contains(fn (array $item): bool => $item['stock_state'] !== 'available'),
        ];
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $this->ensureCartable($product);

        $items = $this->rawItems();
        $nextQuantity = ($items[$product->id] ?? 0) + $quantity;

        if ($nextQuantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah melebihi stok produk yang tersedia.',
            ]);
        }

        $items[$product->id] = $nextQuantity;
        $this->persist($items);
    }

    public function update(Product $product, int $quantity): void
    {
        $this->ensureCartable($product);

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah melebihi stok produk yang tersedia.',
            ]);
        }

        $items = $this->rawItems();
        $items[$product->id] = $quantity;
        $this->persist($items);
    }

    public function remove(Product $product): void
    {
        $items = $this->rawItems();
        unset($items[$product->id]);
        $this->persist($items);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'title' => $product->title,
            'excerpt' => $product->excerpt,
            'description' => $product->description,
            'price' => (float) $product->price,
            'stock' => $product->stock,
            'product_type' => $product->product_type,
            'material' => $product->material,
            'dimensions' => $product->dimensions,
            'location' => $product->location,
            'rating_average' => (float) $product->rating_average,
            'rating_count' => $product->rating_count,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'seller' => $product->seller ? [
                'id' => $product->seller->id,
                'store_name' => $product->seller->store_name,
                'slug' => $product->seller->slug,
                'location' => $product->seller->location,
                'rating_average' => (float) $product->seller->rating_average,
                'rating_count' => $product->seller->rating_count,
            ] : null,
            'image' => $this->imageFor($product),
        ];
    }

    private function stockState(Product $product, int $quantity): string
    {
        if ($product->status !== ProductStatus::Published) {
            return 'unavailable';
        }

        if ($product->stock < $quantity) {
            return 'insufficient';
        }

        return 'available';
    }

    private function ensureCartable(Product $product): void
    {
        if ($product->status !== ProductStatus::Published) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk tidak tersedia.',
            ]);
        }

        if ($product->stock < 1) {
            throw ValidationException::withMessages([
                'product_id' => 'Stok produk sedang kosong.',
            ]);
        }
    }

    /**
     * @param array<int, int> $items
     */
    private function persist(array $items): void
    {
        $clean = collect($items)
            ->mapWithKeys(fn (int $quantity, int $productId) => [(int) $productId => max(1, min(99, $quantity))])
            ->all();

        $this->session->put(self::SESSION_KEY, $clean);
    }

    /**
     * @return array{src: string, alt: string, width: int, height: int}
     */
    private function imageFor(Product $product): array
    {
        $url = $product->getFirstMediaUrl('products') ?: $product->getFirstMediaUrl();

        if (! $url) {
            $fallbacks = [
                'https://images.unsplash.com/photo-1549490349-8643362247b5?q=80&w=900&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1578926375605-eaf7559b1458?q=80&w=900&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1513364776144-60967b0f800f?q=80&w=900&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1578301978693-85fa9c0320b9?q=80&w=900&auto=format&fit=crop',
            ];

            $url = $fallbacks[$product->id % count($fallbacks)];
        }

        return [
            'src' => $url,
            'alt' => $product->title,
            'width' => 900,
            'height' => 1200,
        ];
    }
}
