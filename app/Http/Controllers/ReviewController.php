<?php

namespace App\Http\Controllers;

use App\Events\ReviewSubmitted;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, Book $book)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $user = Auth::user();

        // Check if user has purchased the book and order is completed
        $hasPurchased = $user->orders()
            ->whereHas('items', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })
            ->where('status', 'completed')
            ->exists();

        if (! $hasPurchased) {
            return back()->with('error', 'You can only review books you have purchased.');
        }

        // Create or update review
        Review::updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
            ]
        );

        $review = Review::where('user_id', $user->id)->where('book_id', $book->id)->first();
        if ($review) {
            event(new ReviewSubmitted($review));
        }

        return back()->with('success', 'Review submitted successfully.');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        if (Auth::id() !== $review->user_id && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }
}
