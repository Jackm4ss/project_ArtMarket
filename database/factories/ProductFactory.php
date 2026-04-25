<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->randomElement([
            'Ruang Sunyi Nusantara',
            'Warna Pesisir Sore',
            'Patung Harmoni Kayu',
            'Relief Jejak Kota',
            'Dekorasi Rupa Bumi',
        ]).' '.fake()->unique()->numberBetween(10, 999);

        return [
            'seller_id' => Seller::factory(),
            'category_id' => Category::factory(),
            'sku' => fake()->unique()->bothify('ART-####-??'),
            'title' => $title,
            'excerpt' => fake()->sentence(14),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->numberBetween(350000, 9500000),
            'stock' => fake()->numberBetween(1, 12),
            'status' => ProductStatus::Published,
            'product_type' => fake()->randomElement(['ready', 'preorder']),
            'material' => fake()->randomElement(['Kanvas dan akrilik', 'Kayu jati', 'Batu alam', 'Serat alami', 'Mixed media']),
            'dimensions' => fake()->randomElement(['40 x 60 cm', '60 x 80 cm', '80 x 120 cm', '30 x 30 x 70 cm']),
            'weight_gram' => fake()->numberBetween(500, 15000),
            'location' => fake()->city(),
            'is_featured' => fake()->boolean(30),
            'sold_count' => fake()->numberBetween(0, 150),
            'rating_average' => fake()->randomFloat(2, 4, 5),
            'rating_count' => fake()->numberBetween(0, 100),
            'published_at' => now(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductStatus::Unpublished,
            'published_at' => null,
        ]);
    }
}
