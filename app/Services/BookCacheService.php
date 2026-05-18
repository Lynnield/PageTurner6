<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Book;
use Illuminate\Support\Facades\Cache;

class BookCacheService
{
    private const TTL = 86400; // 24 hours

    /**
     * Cache all active categories.
     */
    public function getCategories()
    {
        return Cache::remember('categories:all', self::TTL, function () {
            return Category::select(['id', 'name', 'description'])->get();
        });
    }

    /**
     * Warm up cache for top categories and bestsellers.
     */
    public function warmUp()
    {
        $this->getCategories();
        
        // Warm up bestsellers
        Cache::forget('books:bestsellers:limit:10');
        
        Book::select(['id', 'title', 'author', 'price', 'isbn', 'cover_image', 'is_bestseller'])
            ->where('is_active', true)
            ->where('is_bestseller', true)
            ->limit(10)
            ->get();
    }

    /**
     * Invalidate related caches.
     */
    public function invalidateBook(Book $book)
    {
        // Without tags, we use specific keys or flush all if necessary
        // For individual book by ISBN
        Cache::forget("books:isbn:{$book->isbn}");
        
        // Bestsellers and other general lists might need full clearing or specific keys
        Cache::forget('books:bestsellers:limit:10');
    }

    /**
     * Invalidate category caches.
     */
    public function invalidateCategories()
    {
        Cache::forget('categories:all');
    }
}
