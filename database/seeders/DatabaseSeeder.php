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

        // Categories
        $categories = Category::factory(8)->create();

        // Books
        $books = Book::factory(40)->recycle($categories)->create();

        // Reviews
        $books->each(function ($book) use ($users) {
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
