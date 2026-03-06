<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@pageturner.com',
        ]);

        // Customers
        $users = User::factory(10)->create();

        $categoryNames = [
            'Fiction',
            'Non-Fiction',
            'Science',
            'Technology',
            'History',
            'Biography',
            'Children’s Literature',
            'Mystery',
            'Romance',
            'Fantasy',
            'Self-Help',
            'Business',
            'Health',
            'Travel',
            'Cooking',
        ];
        $categories = collect($categoryNames)->map(function ($name) {
            return Category::firstOrCreate(
                ['name' => $name],
                ['description' => 'Books for '.$name]
            );
        });

        $faker = \Faker\Factory::create();
        $coverQuery = function (string $name): string {
            $map = [
                'Fiction' => 'novel,fiction,book',
                'Non-Fiction' => 'nonfiction,book',
                'Science' => 'science,lab,books',
                'Technology' => 'technology,code,books',
                'History' => 'history,ancient,books',
                'Biography' => 'biography,portrait,books',
                'Children’s Literature' => 'children,storybook,books',
                'Mystery' => 'mystery,detective,books',
                'Romance' => 'romance,love,books',
                'Fantasy' => 'fantasy,dragon,books',
                'Self-Help' => 'selfhelp,motivation,books',
                'Business' => 'business,finance,books',
                'Health' => 'health,wellness,books',
                'Travel' => 'travel,world,books',
                'Cooking' => 'cooking,recipes,books',
            ];
            $q = $map[$name] ?? 'books';

            return 'https://source.unsplash.com/400x600/?'.$q;
        };
        $categories->each(function (Category $category) use ($faker, $coverQuery) {
            for ($i = 0; $i < 15; $i++) {
                Book::factory()->create([
                    'category_id' => $category->id,
                    'title' => $faker->sentence(3),
                    'author' => collect(range(1, $faker->numberBetween(1, 2)))->map(fn () => $faker->name())->implode(', '),
                    'publisher' => $faker->company(),
                    'publication_year' => $faker->numberBetween(1950, (int) date('Y')),
                    'page_count' => $faker->numberBetween(80, 900),
                    'isbn' => $faker->unique()->isbn13(),
                    'price' => $faker->randomFloat(2, 5, 50),
                    'stock_quantity' => $faker->numberBetween(0, 100),
                    'description' => $faker->sentence(),
                    'cover_image' => $coverQuery($category->name),
                ]);
            }
        });

        // Reviews
        Book::all()->each(function ($book) use ($users) {
            $numReviews = random_int(0, 5);
            if ($numReviews > 0) {
                $reviewers = $users->random(min($numReviews, $users->count()));
                foreach ($reviewers as $reviewer) {
                    Review::factory()->create([
                        'book_id' => $book->id,
                        'user_id' => $reviewer->id,
                    ]);
                }
            }
        });
    }
}
