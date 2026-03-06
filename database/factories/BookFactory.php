<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'title' => fake()->sentence(3),
            'author' => collect(range(1, fake()->numberBetween(1, 2)))->map(fn () => fake()->name())->implode(', '),
            'publisher' => fake()->company(),
            'publication_year' => fake()->numberBetween(1950, (int) date('Y')),
            'page_count' => fake()->numberBetween(80, 900),
            'isbn' => fake()->unique()->isbn13(),
            'price' => fake()->randomFloat(2, 5, 50),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'description' => fake()->sentence(),
            'cover_image' => null,
        ];
    }
}
