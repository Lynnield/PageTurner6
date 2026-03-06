<?php

namespace App\Events;

use App\Models\Review;

class ReviewSubmitted
{
    public function __construct(public Review $review) {}
}
