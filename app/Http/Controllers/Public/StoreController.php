<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function show(Seller $seller): Response
    {
        $seller->load([
            'products' => fn ($query) => $query
                ->published()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(24),
        ]);

        return Inertia::render('Public/StoreShow', [
            'seller' => $seller,
        ]);
    }
}
