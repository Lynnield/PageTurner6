<?php

namespace App\Exports;

use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    /**
     * @param  array<string,mixed>  $filters
     * @param  list<string>  $columns
     */
    public function __construct(
        private readonly array $filters,
        private readonly array $columns
    ) {}

    public function query(): Builder
    {
        $q = Book::query()->with('category');

        if (! empty($this->filters['category'])) {
            $q->whereHas('category', fn (Builder $cq) => $cq->where('name', $this->filters['category']));
        }

        if (Arr::has($this->filters, ['price_min', 'price_max'])) {
            $min = $this->filters['price_min'];
            $max = $this->filters['price_max'];
            if ($min !== null && $min !== '') {
                $q->where('price', '>=', (float) $min);
            }
            if ($max !== null && $max !== '') {
                $q->where('price', '<=', (float) $max);
            }
        }

        if (! empty($this->filters['stock_status'])) {
            if ($this->filters['stock_status'] === 'in_stock') {
                $q->where('stock_quantity', '>', 0);
            } elseif ($this->filters['stock_status'] === 'out_of_stock') {
                $q->where('stock_quantity', '=', 0);
            }
        }

        if (! empty($this->filters['date_from'])) {
            $q->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $q->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $q->orderBy('id');
    }

    public function headings(): array
    {
        return array_map([$this, 'headingFor'], $this->columns);
    }

    public function map($book): array
    {
        /** @var \App\Models\Book $book */
        $out = [];
        foreach ($this->columns as $col) {
            $out[] = $this->valueFor($col, $book);
        }

        return $out;
    }

    private function headingFor(string $col): string
    {
        return match ($col) {
            'id' => 'ID',
            'isbn' => 'ISBN',
            'title' => 'Title',
            'author' => 'Author',
            'price' => 'Price',
            'stock' => 'Stock',
            'category' => 'Category',
            'description' => 'Description',
            'created_at' => 'Created At',
            default => $col,
        };
    }

    private function valueFor(string $col, Book $book): mixed
    {
        return match ($col) {
            'id' => $book->id,
            'isbn' => $book->isbn,
            'title' => $book->title,
            'author' => $book->author,
            'price' => (string) $book->price,
            'stock' => $book->stock_quantity,
            'category' => $book->category?->name,
            'description' => $book->description,
            'created_at' => optional($book->created_at)->toDateTimeString(),
            default => data_get($book, $col),
        };
    }
}

