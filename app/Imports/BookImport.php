<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\Category;
use App\Models\ImportLog;
use App\Models\ImportLogFailure;
use App\Rules\IsbnRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class BookImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading, ShouldQueue, WithEvents, SkipsOnFailure, SkipsOnError, SkipsEmptyRows, WithBatchInserts
{
    /**
     * Enterprise note:
     * - Validation happens row-by-row, invalid rows are skipped and recorded in `import_log_failures`.
     * - Processing uses chunked upserts/inserts to stay within <256MB memory.
     */
    public function __construct(
        private readonly int $importLogId,
        private readonly string $mode // skip|update
    ) {}

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            'isbn' => [
                'required',
                new IsbnRule(),
                function (string $attribute, mixed $value, \Closure $fail) {
                    $isbn = preg_replace('/[^0-9Xx]/', '', (string) $value) ?? '';
                    if ($isbn === '') {
                        return;
                    }
                    if ($this->mode === 'skip' && Book::where('isbn', $isbn)->exists()) {
                        $fail('Duplicate ISBN.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'gt:0', 'lte:9999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string', Rule::exists('categories', 'name')],
            'description' => ['nullable', 'string'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'isbn' => 'ISBN',
            'title' => 'Title',
            'author' => 'Author',
            'price' => 'Price',
            'stock' => 'Stock',
            'category' => 'Category',
            'description' => 'Description',
        ];
    }

    public function collection(Collection $rows): void
    {
        $now = Carbon::now();

        // Cache categories for this chunk.
        $categoryNames = $rows->pluck('category')
            ->filter(fn ($v) => is_string($v) && $v !== '')
            ->unique()
            ->values();

        $categoryMap = Category::whereIn('name', $categoryNames)->pluck('id', 'name');

        $payload = [];
        $isbns = [];

        foreach ($rows as $row) {
            $isbn = preg_replace('/[^0-9Xx]/', '', (string) ($row['isbn'] ?? '')) ?? '';
            if ($isbn === '') {
                continue;
            }

            $categoryName = (string) ($row['category'] ?? '');
            $categoryId = $categoryMap[$categoryName] ?? null;

            if (! $categoryId) {
                // Should be caught by validation, but keep it defensive.
                $this->logFailure(null, 'category', ['Category does not exist.'], $row);
                continue;
            }

            $payload[] = [
                'category_id' => (int) $categoryId,
                'title' => (string) ($row['title'] ?? ''),
                'author' => (string) ($row['author'] ?? ''),
                'isbn' => $isbn,
                'price' => (float) ($row['price'] ?? 0),
                'stock_quantity' => (int) ($row['stock'] ?? 0),
                'description' => $row['description'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $isbns[] = $isbn;
        }

        if ($payload === []) {
            return;
        }

        DB::transaction(function () use ($payload, $isbns) {
            if ($this->mode === 'update') {
                Book::upsert(
                    $payload,
                    ['isbn'],
                    ['category_id', 'title', 'author', 'price', 'stock_quantity', 'description', 'updated_at']
                );
                ImportLog::whereKey($this->importLogId)->update([
                    'processed_rows' => DB::raw('processed_rows + '.count($payload)),
                    'success_rows' => DB::raw('success_rows + '.count($payload)),
                ]);

                return;
            }

            // skip mode: insert only missing ISBNs (avoid per-row queries).
            $existing = Book::whereIn('isbn', $isbns)->pluck('isbn')->all();
            $existingSet = array_fill_keys($existing, true);

            $toInsert = [];
            $skipped = 0;

            foreach ($payload as $row) {
                if (isset($existingSet[$row['isbn']])) {
                    $skipped++;
                    continue;
                }
                $toInsert[] = $row;
            }

            if ($toInsert !== []) {
                // Batch insert of up to chunk size (1000).
                Book::insert($toInsert);
            }

            ImportLog::whereKey($this->importLogId)->update([
                'processed_rows' => DB::raw('processed_rows + '.count($payload)),
                'success_rows' => DB::raw('success_rows + '.count($toInsert)),
                'failed_rows' => DB::raw('failed_rows + '.(int) $skipped),
            ]);

            if ($skipped > 0) {
                Log::info('Book import skipped duplicate ISBNs', [
                    'import_log_id' => $this->importLogId,
                    'skipped' => $skipped,
                ]);
            }
        });
    }

    public function onFailure(Failure ...$failures): void
    {
        $rows = [];
        foreach ($failures as $failure) {
            $rows[] = [
                'import_log_id' => $this->importLogId,
                'row_number' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => json_encode($failure->errors(), JSON_UNESCAPED_UNICODE),
                'values' => json_encode($failure->values(), JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert for scalability.
        ImportLogFailure::insert($rows);

        ImportLog::whereKey($this->importLogId)->update([
            'failed_rows' => DB::raw('failed_rows + '.count($failures)),
        ]);
    }

    public function onError(Throwable $e): void
    {
        Log::error('Book import chunk error', [
            'import_log_id' => $this->importLogId,
            'error' => $e->getMessage(),
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function () {
                ImportLog::whereKey($this->importLogId)->update([
                    'status' => 'processing',
                    'started_at' => now(),
                ]);
            },
            AfterImport::class => function (AfterImport $event) {
                $log = ImportLog::find($this->importLogId);
                if (! $log) {
                    return;
                }

                $status = ((int) $log->failed_rows) > 0 ? 'completed_with_errors' : 'completed';
                $log->update([
                    'status' => $status,
                    'finished_at' => now(),
                ]);
            },
            ImportFailed::class => function (ImportFailed $event) {
                ImportLog::whereKey($this->importLogId)->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'error_message' => (string) $event->getException()->getMessage(),
                ]);
            },
        ];
    }

    private function logFailure(?int $rowNumber, ?string $attribute, array $errors, mixed $values): void
    {
        try {
            ImportLogFailure::create([
                'import_log_id' => $this->importLogId,
                'row_number' => $rowNumber,
                'attribute' => $attribute,
                'errors' => $errors,
                'values' => is_array($values) ? $values : (array) $values,
            ]);
            ImportLog::whereKey($this->importLogId)->update([
                'failed_rows' => DB::raw('failed_rows + 1'),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to record import failure', [
                'import_log_id' => $this->importLogId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

