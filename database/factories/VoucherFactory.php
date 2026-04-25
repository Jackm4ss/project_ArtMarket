<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('ART###')),
            'name' => fake()->words(3, true),
            'type' => 'fixed',
            'value' => fake()->numberBetween(25000, 150000),
            'minimum_order_amount' => 0,
            'max_discount_amount' => null,
            'usage_limit' => null,
            'per_user_limit' => null,
            'used_count' => 0,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
        ];
    }

    public function percent(float $value = 10, ?float $maxDiscountAmount = null): static
    {
        return $this->state(fn (): array => [
            'type' => 'percent',
            'value' => $value,
            'max_discount_amount' => $maxDiscountAmount,
        ]);
    }
}
