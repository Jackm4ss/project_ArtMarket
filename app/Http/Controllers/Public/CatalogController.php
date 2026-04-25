<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Queries\Catalog\ProductCatalogQuery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __invoke(Request $request, ProductCatalogQuery $catalog): Response
    {
        return Inertia::render('Public/Catalog', [
            'products' => $catalog->paginate($request),
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'filters' => $request->only([
                'q',
                'category',
                'seller',
                'material',
                'location',
                'type',
                'promo',
                'min_price',
                'max_price',
                'seller_rating',
                'sort',
            ]),
        ]);
    }
}
