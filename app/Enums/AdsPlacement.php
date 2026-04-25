<?php

namespace App\Enums;

enum AdsPlacement: string
{
    case HomepageFeatured = 'homepage_featured';
    case CatalogTop = 'catalog_top';
    case SellerSpotlight = 'seller_spotlight';

    public function label(): string
    {
        return match ($this) {
            self::HomepageFeatured => 'Homepage Featured',
            self::CatalogTop => 'Catalog Top',
            self::SellerSpotlight => 'Seller Spotlight',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $placement): array => [$placement->value => $placement->label()])
            ->all();
    }
}
