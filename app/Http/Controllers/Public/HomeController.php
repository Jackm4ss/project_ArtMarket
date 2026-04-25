<?php

namespace App\Http\Controllers\Public;

use App\Enums\BannerPlacement;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $banners = Banner::query()
            ->active()
            ->whereIn('placement', [BannerPlacement::HomeHero, BannerPlacement::HomeFeatured])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (Banner $banner): string => $banner->placement->value);

        return Inertia::render('Public/Home', [
            'banners' => [
                'home_hero' => $banners->get(BannerPlacement::HomeHero->value, collect())->values(),
                'home_featured' => $banners->get(BannerPlacement::HomeFeatured->value, collect())->values(),
            ],
        ]);
    }
}
