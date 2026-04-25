<?php

namespace App\Http\Controllers\Seller;

use App\Enums\AdsPlacement;
use App\Enums\AdsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerAdRequest;
use App\Models\Product;
use App\Models\SellerAd;
use App\Services\Notifications\MarketplaceNotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SellerAdController extends Controller
{
    public function index(Request $request): View
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        return view('seller.ads', [
            'ads' => SellerAd::query()
                ->with(['product:id,title,slug'])
                ->where('seller_id', $seller->id)
                ->latest('id')
                ->paginate(15),
            'products' => Product::query()
                ->where('seller_id', $seller->id)
                ->whereNull('deleted_at')
                ->latest('id')
                ->get(['id', 'title']),
            'placements' => AdsPlacement::options(),
        ]);
    }

    public function store(SellerAdRequest $request, MarketplaceNotificationService $notifications): RedirectResponse
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        $ad = SellerAd::query()->create([
            ...$request->validated(),
            'seller_id' => $seller->id,
            'status' => AdsStatus::Pending,
        ]);
        $notifications->sellerAdRequested($ad->load('seller'));

        return back()->with('status', 'Request iklan dikirim. Admin akan meninjau placement dan jadwalnya.');
    }

    public function destroy(Request $request, SellerAd $sellerAd): RedirectResponse
    {
        $seller = $request->user()?->seller;

        abort_unless($seller && $sellerAd->seller_id === $seller->id, 403);

        if (! in_array($sellerAd->status, [AdsStatus::Pending, AdsStatus::Rejected], true)) {
            throw ValidationException::withMessages([
                'ad' => 'Iklan aktif atau sudah selesai tidak bisa dihapus dari seller area.',
            ]);
        }

        $sellerAd->delete();

        return back()->with('status', 'Request iklan dihapus.');
    }
}
