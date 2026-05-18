<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    private static ?array $categoryIds = null;
    private static array $publishers = [
        'Penguin Random House', 'HarperCollins', 'Simon & Schuster', 'Hachette Livre', 
        'Macmillan Publishers', 'Oxford University Press', 'Cambridge University Press',
        'Scholastic', 'Pearson Education', 'Bloomsbury Publishing'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (self::$categoryIds === null) {
            self::$categoryIds = Category::pluck('id')->toArray();
            if (empty(self::$categoryIds)) {
                self::$categoryIds = [Category::factory()->create()->id];
            }
        }

        $isBestseller = fake()->boolean(10);
        $format = fake()->randomElement(['Hardcover', 'Paperback', 'E-book']);
        
        // Format-based pricing logic
        $basePrice = match($format) {
            'Hardcover' => fake()->randomFloat(2, 25, 45),
            'Paperback' => fake()->randomFloat(2, 10, 24.99),
            'E-book' => fake()->randomFloat(2, 2.99, 15),
            default => fake()->randomFloat(2, 5, 50),
        };

        return [
            'category_id' => fake()->randomElement(self::$categoryIds),
            'title' => fake()->unique()->sentence(fake()->numberBetween(2, 5)),
            'author' => fake()->name(),
            'publisher' => fake()->randomElement(self::$publishers),
            'publication_year' => fake()->numberBetween(1900, (int) date('Y')),
            'page_count' => fake()->numberBetween(100, 1200),
            'isbn' => $this->generateIsbn13(),
            'price' => $basePrice,
            'stock_quantity' => fake()->numberBetween(0, 500),
            'description' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'is_active' => true,
            'is_bestseller' => $isBestseller,
            'published_at' => fake()->dateTimeBetween('-10 years', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate a valid ISBN-13 string.
     */
    private function generateIsbn13(): string
    {
        $prefix = '978';
        $body = str_pad((string)fake()->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT);
        $digits = $prefix . $body;
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$digits[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $digits . $checkDigit;
    }

    /**
     * Indicate that the book is a bestseller.
     */
    public function bestseller(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_bestseller' => true,
        ]);
    }
}
