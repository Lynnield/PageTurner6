<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Seed the application's database with test data.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create test customers
        User::factory(10)->create([
            'email_verified_at' => now(),
            'api_tier' => 'standard',
        ]);

        // Create categories
        $categories = Category::factory(5)->create();

        // Create books
        Book::factory(50)->create([
            'category_id' => fn() => $categories->random()->id,
        ]);

        // Create orders with items
        $customers = User::where('role', '!=', 'admin')->get();
        foreach ($customers as $customer) {
            $books = Book::inRandomOrder()->limit(3)->get();
            
            $order = Order::factory()->create([
                'user_id' => $customer->id,
                'status' => ['pending', 'processing', 'completed', 'cancelled'][array_rand([0, 1, 2, 3])],
            ]);

            foreach ($books as $book) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $book->id,
                    'quantity' => rand(1, 3),
                    'unit_price' => $book->price,
                ]);
            }
        }

        // Create reviews
        Review::factory(30)->create();

        $this->command->info('Test data seeded successfully!');
    }
}
