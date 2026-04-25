<?php

namespace App\Http\Controllers\Seller;

use App\Enums\ReferralStatus;
use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Services\Referrals\ReferralService;
use App\Support\MarketplaceConfig;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SellerReferralController extends Controller
{
    public function __invoke(Request $request, ReferralService $referralService): View
    {
        $seller = $request->user()?->seller;

        abort_unless($seller, 403);

        $referralCode = $referralService->codeForSeller($seller);
        $referralLink = route('seller.register', ['ref' => $referralCode]);

        $referrals = Referral::query()
            ->with('referred:id,name,email')
            ->where('referrer_id', $request->user()->id)
            ->latest('id')
            ->paginate(15);

        return view('seller.referrals', [
            'seller' => $seller,
            'referralCode' => $referralCode,
            'referralLink' => $referralLink,
            'rewardAmount' => MarketplaceConfig::referralRewardAmount(),
            'pendingCount' => Referral::query()
                ->where('referrer_id', $request->user()->id)
                ->where('status', ReferralStatus::Pending)
                ->count(),
            'qualifiedCount' => Referral::query()
                ->where('referrer_id', $request->user()->id)
                ->where('status', ReferralStatus::Qualified)
                ->count(),
            'rewardedCount' => Referral::query()
                ->where('referrer_id', $request->user()->id)
                ->where('status', ReferralStatus::Rewarded)
                ->count(),
            'rewardTotal' => (float) Referral::query()
                ->where('referrer_id', $request->user()->id)
                ->where('status', ReferralStatus::Rewarded)
                ->sum('reward_amount'),
            'referrals' => $referrals,
        ]);
    }
}
