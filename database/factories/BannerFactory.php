<?php

namespace Database\Factories;

use App\Enums\BannerPlacement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Banner>
 */
class BannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'placement' => BannerPlacement::HomeHero,
            'image_path' => null,
            'link_url' => fake()->url(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
