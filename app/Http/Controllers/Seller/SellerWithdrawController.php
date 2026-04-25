<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\WithdrawRequest;
use App\Models\Withdraw;
use App\Services\Wallet\WalletLedgerService;
use App\Services\Withdraws\WithdrawService;
use App\Support\MarketplaceConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SellerWithdrawController extends Controller
{
    public function index(Request $request, WalletLedgerService $walletLedgers): View
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        $withdraws = Withdraw::query()
            ->where('seller_id', $seller->id)
            ->latest('id')
            ->paginate(15);

        return view('seller.withdrawals', [
            'available' => $walletLedgers->availableBalance($seller->id),
            'withdraws' => $withdraws,
            'withdrawMinimum' => MarketplaceConfig::withdrawMinimum(),
            'withdrawFee' => MarketplaceConfig::withdrawFee(),
            'seller' => $seller,
        ]);
    }

    public function store(WithdrawRequest $request, WithdrawService $withdraws): RedirectResponse
    {
        $withdraws->request($request->user()->seller, (float) $request->validated('amount'));

        return back()->with('status', 'Request withdraw dibuat dan menunggu approval admin.');
    }
}
