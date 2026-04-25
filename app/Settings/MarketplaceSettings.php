<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MarketplaceSettings extends Settings
{
    public string $currency;

    public float $commission_rate;

    public string $shipping_mode;

    public bool $product_auto_publish;

    public float $referral_reward_amount;

    public float $withdraw_minimum;

    public float $withdraw_fee;

    public string $withdraw_schedule;

    public static function group(): string
    {
        return 'marketplace';
    }
}
