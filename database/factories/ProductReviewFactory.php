<?php

namespace Database\Factories;

use App\Enums\ProductReviewStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductReview>
 */
class ProductReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_item_id' => null,
            'rating' => fake()->numberBetween(4, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => ProductReviewStatus::Published,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => [
            'status' => ProductReviewStatus::Hidden,
        ]);
    }
}
