<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(4, true),
            'status' => ArticleStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ArticleStatus::Published,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function futurePublished(): static
    {
        return $this->state(fn (): array => [
            'status' => ArticleStatus::Published,
            'published_at' => now()->addDay(),
        ]);
    }
}
