<?php

namespace App\Queries\Catalog;

use App\Models\Product;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductCatalogQuery
{
    public function paginate(Request $request, int $perPage = 24): CursorPaginator
    {
        return $this->baseQuery($request)
            ->cursorPaginate($perPage)
            ->withQueryString();
    }

    public function baseQuery(Request $request): Builder
    {
        $query = Product::query()
            ->with([
                'seller:id,store_name,slug,rating_average,rating_count,location',
                'category:id,name,slug',
                'media',
            ])
            ->published();

        $this->applyFilters($query, $request);
        $this->applySort($query, (string) $request->query('sort', 'latest'));

        return $query;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $search = trim((string) $request->query('q', ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhereHas('seller', fn (Builder $seller): Builder => $seller->where('store_name', 'like', "%{$search}%"));
            });
        }

        $query->when($request->filled('category'), function (Builder $query) use ($request): void {
            $query->whereHas('category', fn (Builder $category): Builder => $category->where('slug', $request->query('category')));
        });

        $query->when($request->filled('seller'), function (Builder $query) use ($request): void {
            $query->whereHas('seller', fn (Builder $seller): Builder => $seller->where('slug', $request->query('seller')));
        });

        $query->when($request->filled('material'), fn (Builder $query): Builder => $query->where('material', $request->query('material')));
        $query->when($request->filled('location'), fn (Builder $query): Builder => $query->where('location', $request->query('location')));
        $query->when($request->filled('type'), fn (Builder $query): Builder => $query->where('product_type', $request->query('type')));
        $query->when($request->boolean('promo'), fn (Builder $query): Builder => $query->whereNotNull('compare_at_price'));

        $query->when($request->filled('min_price'), fn (Builder $query): Builder => $query->where('price', '>=', (int) $request->query('min_price')));
        $query->when($request->filled('max_price'), fn (Builder $query): Builder => $query->where('price', '<=', (int) $request->query('max_price')));

        $query->when($request->filled('seller_rating'), function (Builder $query) use ($request): void {
            $query->whereHas('seller', fn (Builder $seller): Builder => $seller->where('rating_average', '>=', (float) $request->query('seller_rating')));
        });
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'popularity', 'reviews' => $query->orderByDesc('sold_count')->orderByDesc('rating_average')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }
}
