<?php

namespace Tests\Feature\Commerce;

use App\Enums\BannerPlacement;
use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_receives_only_active_home_banners(): void
    {
        $active = Banner::factory()->create([
            'title' => 'Hero campaign aktif',
            'placement' => BannerPlacement::HomeHero,
            'sort_order' => 1,
        ]);
        Banner::factory()->inactive()->create([
            'title' => 'Hero campaign nonaktif',
            'placement' => BannerPlacement::HomeHero,
        ]);
        Banner::factory()->create([
            'title' => 'Catalog campaign',
            'placement' => BannerPlacement::CatalogTop,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('banners.home_hero', 1)
                ->where('banners.home_hero.0.id', $active->id)
                ->has('banners.home_featured', 0)
            );
    }
}
