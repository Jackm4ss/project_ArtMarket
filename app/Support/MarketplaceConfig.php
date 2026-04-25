<?php

namespace App\Support;

use App\Settings\MarketplaceSettings;
use Throwable;

final class MarketplaceConfig
{
    public static function currency(): string
    {
        return (string) self::value('currency', 'IDR');
    }

    public static function commissionRate(): float
    {
        return (float) self::value('commission_rate', 0.10);
    }

    public static function referralRewardAmount(): float
    {
        return (float) self::value('referral_reward_amount', 50000);
    }

    public static function withdrawMinimum(): float
    {
        return (float) self::value('withdraw_minimum', 100000);
    }

    public static function withdrawFee(): float
    {
        return (float) self::value('withdraw_fee', 6500);
    }

    private static function value(string $key, mixed $fallback): mixed
    {
        $fallback = config("marketplace.{$key}", $fallback);

        if (app()->runningUnitTests()) {
            return $fallback;
        }

        try {
            /** @var MarketplaceSettings $settings */
            $settings = app(MarketplaceSettings::class);

            return $settings->{$key};
        } catch (Throwable) {
            return $fallback;
        }
    }
}
