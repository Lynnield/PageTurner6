<?php

namespace Tests\Performance;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BookCatalogLoadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed some data for performance testing
        \Database\Seeders\CategorySeeder::class;
        Book::factory()->count(100)->create();
    }

    public function test_catalog_response_time()
    {
        $start = microtime(true);
        $response = $this->getJson('/api/books');
        $duration = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(200, $duration, "Catalog response took too long: {$duration}ms");
    }

    public function test_isbn_lookup_is_cached()
    {
        $book = Book::first();
        
        // First request - should populate cache
        $this->getJson("/api/books/isbn/{$book->isbn}");
        
        $this->assertTrue(Cache::tags(['books', 'isbn'])->has("books:isbn:{$book->isbn}"));
        
        // Second request - should be served from cache
        $start = microtime(true);
        $this->getJson("/api/books/isbn/{$book->isbn}");
        $duration = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(50, $duration, "Cached ISBN lookup took too long: {$duration}ms");
    }

    public function test_rate_limiting_works()
    {
        $user = User::factory()->create(['api_tier' => 'standard']);
        $this->actingAs($user);

        // Standard tier limit is 60/min
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/books');
        }

        $response = $this->getJson('/api/books');
        $response->assertStatus(429);
    }
}
