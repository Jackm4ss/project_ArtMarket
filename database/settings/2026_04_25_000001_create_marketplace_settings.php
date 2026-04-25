<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('marketplace.currency', config('marketplace.currency', 'IDR'));
        $this->migrator->add('marketplace.commission_rate', (float) config('marketplace.commission_rate', 0.10));
        $this->migrator->add('marketplace.shipping_mode', config('marketplace.shipping_mode', 'manual'));
        $this->migrator->add('marketplace.product_auto_publish', (bool) config('marketplace.product_auto_publish', true));
        $this->migrator->add('marketplace.referral_reward_amount', (float) config('marketplace.referral_reward_amount', 50000));
        $this->migrator->add('marketplace.withdraw_minimum', (float) config('marketplace.withdraw_minimum', 100000));
        $this->migrator->add('marketplace.withdraw_fee', (float) config('marketplace.withdraw_fee', 6500));
        $this->migrator->add('marketplace.withdraw_schedule', config('marketplace.withdraw_schedule', 'weekly'));
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('marketplace.currency');
        $this->migrator->deleteIfExists('marketplace.commission_rate');
        $this->migrator->deleteIfExists('marketplace.shipping_mode');
        $this->migrator->deleteIfExists('marketplace.product_auto_publish');
        $this->migrator->deleteIfExists('marketplace.referral_reward_amount');
        $this->migrator->deleteIfExists('marketplace.withdraw_minimum');
        $this->migrator->deleteIfExists('marketplace.withdraw_fee');
        $this->migrator->deleteIfExists('marketplace.withdraw_schedule');
    }
};
