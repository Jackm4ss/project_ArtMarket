<?php

namespace App\Enums;

enum BannerPlacement: string
{
    case HomeHero = 'home_hero';
    case HomeFeatured = 'home_featured';
    case CatalogTop = 'catalog_top';
    case ArticleTop = 'article_top';

    public function label(): string
    {
        return match ($this) {
            self::HomeHero => 'Home hero',
            self::HomeFeatured => 'Home featured',
            self::CatalogTop => 'Catalog top',
            self::ArticleTop => 'Article top',
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
