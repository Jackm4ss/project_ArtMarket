<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case Pending = 'pending';
    case Qualified = 'qualified';
    case Rewarded = 'rewarded';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Qualified => 'Qualified',
            self::Rewarded => 'Rewarded',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
