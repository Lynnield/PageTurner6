<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'price' => (float) $this->price,
            'cover_image' => $this->cover_image_url,
            'is_bestseller' => (bool) $this->is_bestseller,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'average_rating' => $this->when(isset($this->reviews_avg_rating), round($this->reviews_avg_rating, 1)),
            'stock' => $this->when($request->user()?->isAdmin(), $this->stock_quantity),
        ];
    }
}
