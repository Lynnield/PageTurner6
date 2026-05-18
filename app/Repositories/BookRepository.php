<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\CursorPaginator;

class BookRepository
{
    private const CACHE_PREFIX = 'books:';
    private const TTL = 3600; // 1 hour

    /**
     * Get optimized books for catalog browsing with cursor pagination.
     */
    public function getCatalog(array $filters = [], int $perPage = 20): CursorPaginator
    {
        $query = Book::query()
            ->select(['id', 'title', 'author', 'price', 'isbn', 'cover_image', 'category_id', 'is_bestseller', 'published_at'])
            ->with(['category:id,name'])
            ->where('is_active', true);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        return $query->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);
    }

    /**
     * Find a book by ISBN with caching.
     */
    public function findByIsbn(string $isbn): ?Book
    {
        return Cache::remember(
            self::CACHE_PREFIX . "isbn:{$isbn}",
            self::TTL,
            fn () => Book::with('category')
                ->where('isbn', $isbn)
                ->where('is_active', true)
                ->first()
        );
    }

    /**
     * Get bestseller books with caching.
     */
    public function getBestsellers(int $limit = 10)
    {
        return Cache::remember(
            self::CACHE_PREFIX . "bestsellers:limit:{$limit}",
            self::TTL,
            fn () => Book::with('category')
                ->select(['id', 'title', 'author', 'price', 'isbn', 'cover_image', 'is_bestseller', 'category_id'])
                ->where('is_active', true)
                ->where('is_bestseller', true)
                ->limit($limit)
                ->get()
        );
    }
}
