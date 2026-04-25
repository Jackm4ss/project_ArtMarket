<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerStoreRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellerStoreController extends Controller
{
    public function edit(Request $request): View
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        return view('seller.store', [
            'seller' => $seller,
        ]);
    }

    public function update(SellerStoreRequest $request): RedirectResponse
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        $seller->update($request->validated());

        return back()->with('status', 'Profil toko diperbarui.');
    }
}
