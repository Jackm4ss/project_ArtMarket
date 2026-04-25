<?php

namespace App\Http\Controllers\Seller;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellerProductController extends Controller
{
    public function index(Request $request): View
    {
        $seller = $request->user()?->seller;

        $products = Product::query()
            ->with(['category:id,name', 'seller:id,store_name', 'media'])
            ->when(! $request->user()?->hasRole('admin'), fn ($query) => $query->where('seller_id', $seller?->id))
            ->latest('id')
            ->paginate(15);

        return view('seller.products', ['products' => $products]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->seller, 403);

        return view('seller.product-form', [
            'product' => new Product([
                'product_type' => 'ready',
                'stock' => 1,
            ]),
            'categories' => $this->categories(),
            'action' => route('seller.products.store'),
            'method' => 'POST',
            'title' => 'Tambah Produk',
        ]);
    }

    public function store(SellerProductRequest $request): RedirectResponse
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        $product = Product::query()->create([
            ...$request->productData(),
            'seller_id' => $seller->id,
            'status' => ProductStatus::Published,
            'published_at' => now(),
        ]);

        $this->syncImage($request, $product);

        return redirect()
            ->route('seller.products.edit', $product)
            ->with('status', 'Produk dibuat dan langsung dipublish.');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorizeProduct($request, $product);
        $product->load('media');

        return view('seller.product-form', [
            'product' => $product,
            'categories' => $this->categories(),
            'action' => route('seller.products.update', $product),
            'method' => 'PATCH',
            'title' => 'Edit Produk',
        ]);
    }

    public function update(SellerProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        $product->update($request->productData());
        $this->syncImage($request, $product);

        return back()->with('status', 'Produk diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        $product->delete();

        return redirect()
            ->route('seller.products.index')
            ->with('status', 'Produk dihapus dari katalog seller.');
    }

    public function updateStock(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($request, $product);

        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $product->update(['stock' => $validated['stock']]);

        return back()->with('status', 'Stok produk diperbarui.');
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        $seller = $request->user()?->seller;

        abort_unless($request->user()?->hasRole('admin') || $product->seller_id === $seller?->id, 403);
    }

    private function syncImage(SellerProductRequest $request, Product $product): void
    {
        if ($request->boolean('remove_image')) {
            $product->clearMediaCollection('products');
        }

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('products');
            $product
                ->addMediaFromRequest('image')
                ->toMediaCollection('products');
        }
    }

    private function categories()
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
