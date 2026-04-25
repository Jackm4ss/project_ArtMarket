<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Withdraw;
use App\Models\WalletLedger;
use App\Support\MarketplaceConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SellerWalletController extends Controller
{
    public function __invoke(Request $request): View
    {
        $seller = $request->user()?->seller;

        $query = WalletLedger::query()
            ->with(['seller:id,store_name'])
            ->when(! $request->user()?->hasRole('admin'), fn ($builder) => $builder->where('seller_id', $seller?->id));

        $available = (clone $query)->whereIn('type', ['escrow_available', 'withdraw_rejected', 'refund_reversed'])->sum('amount')
            - (clone $query)->whereIn('type', ['withdraw_requested', 'refund_debited'])->sum('amount');

        return view('seller.wallet', [
            'available' => $available,
            'ledgers' => $query->latest('occurred_at')->paginate(20),
            'withdraws' => Withdraw::query()
                ->with('seller:id,store_name')
                ->when(! $request->user()?->hasRole('admin'), fn ($builder) => $builder->where('seller_id', $seller?->id))
                ->latest('id')
                ->limit(10)
                ->get(),
            'withdrawMinimum' => MarketplaceConfig::withdrawMinimum(),
            'withdrawFee' => MarketplaceConfig::withdrawFee(),
            'seller' => $seller,
        ]);
    }
}
