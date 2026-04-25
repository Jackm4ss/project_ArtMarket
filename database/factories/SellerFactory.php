<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Seller>
 */
class SellerFactory extends Factory
{
    public function definition(): array
    {
        $storeName = fake()->company().' Studio';

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'bio' => fake()->paragraph(),
            'status' => 'active',
            'location' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'bank_name' => 'BCA',
            'bank_account_name' => $storeName,
            'bank_account_number' => fake()->numerify('##########'),
            'rating_average' => fake()->randomFloat(2, 4, 5),
            'rating_count' => fake()->numberBetween(3, 80),
            'verified_at' => now(),
        ];
    }
}
