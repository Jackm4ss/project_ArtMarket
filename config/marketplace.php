<?php

return [
    'currency' => env('MARKETPLACE_CURRENCY', 'IDR'),
    'commission_rate' => (float) env('MARKETPLACE_COMMISSION_RATE', 0.10),
    'shipping_mode' => env('MARKETPLACE_SHIPPING_MODE', 'manual'),
    'product_auto_publish' => (bool) env('MARKETPLACE_PRODUCT_AUTO_PUBLISH', true),
    'referral_reward_amount' => (float) env('MARKETPLACE_REFERRAL_REWARD_AMOUNT', 50000),
    'withdraw_minimum' => (float) env('MARKETPLACE_WITHDRAW_MINIMUM', 100000),
    'withdraw_fee' => (float) env('MARKETPLACE_WITHDRAW_FEE', 6500),
    'withdraw_schedule' => env('MARKETPLACE_WITHDRAW_SCHEDULE', 'weekly'),
];
