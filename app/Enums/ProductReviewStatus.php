<?php

namespace App\Enums;

enum ProductReviewStatus: string
{
    case Published = 'published';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Published => 'Published',
            self::Hidden => 'Hidden',
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
