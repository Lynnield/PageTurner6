<?php

namespace App\Observers;

use App\Models\Book;
use App\Services\BookCacheService;

class BookObserver
{
    public function __construct(private BookCacheService $cacheService) {}

    /**
     * Handle the Book "created" event.
     */
    public function created(Book $book): void
    {
        $this->cacheService->invalidateBook($book);
    }

    /**
     * Handle the Book "updated" event.
     */
    public function updated(Book $book): void
    {
        $this->cacheService->invalidateBook($book);
    }

    /**
     * Handle the Book "deleted" event.
     */
    public function deleted(Book $book): void
    {
        $this->cacheService->invalidateBook($book);
    }
}
