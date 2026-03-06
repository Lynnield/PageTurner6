<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user, Book $book): bool
    {
        if (is_null($user->email_verified_at)) {
            return false;
        }

        return $user->orders()
            ->whereHas('items', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })
            ->where('status', 'completed')
            ->exists();
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isAdmin() || $user->id === $review->user_id;
    }
}
