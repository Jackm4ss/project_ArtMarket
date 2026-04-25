<?php

namespace App\Services\Ads;

use App\Enums\AdsStatus;
use App\Models\SellerAd;
use App\Services\Notifications\MarketplaceNotificationService;

class SellerAdModerationService
{
    public function __construct(private readonly MarketplaceNotificationService $notifications)
    {
    }

    public function activate(SellerAd $ad): SellerAd
    {
        $ad->forceFill([
            'status' => AdsStatus::Active,
            'starts_at' => $ad->starts_at ?? now(),
        ])->save();

        $ad = $ad->refresh();
        $this->notifications->sellerAdActivated($ad);

        return $ad;
    }

    public function reject(SellerAd $ad, ?string $adminNote = null): SellerAd
    {
        $ad->forceFill([
            'status' => AdsStatus::Rejected,
            'admin_note' => $adminNote,
        ])->save();

        $ad = $ad->refresh();
        $this->notifications->sellerAdRejected($ad);

        return $ad;
    }

    public function expire(SellerAd $ad): SellerAd
    {
        $ad->forceFill([
            'status' => AdsStatus::Expired,
            'ends_at' => $ad->ends_at ?? now(),
        ])->save();

        $ad = $ad->refresh();
        $this->notifications->sellerAdExpired($ad);

        return $ad;
    }
}
