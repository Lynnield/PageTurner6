<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use App\Models\ImportLog;

class UserImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, WithEvents
{
    public function __construct(
        private int $importLogId = 0
    ) {}

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $user = new User([
            'name'     => $row['name'],
            'email'    => $row['email'],
            'password' => Hash::make($row['password'] ?? 'default123'),
            'role'     => $row['role'] ?? 'customer',
        ]);

        if ($this->importLogId) {
            ImportLog::whereKey($this->importLogId)->increment('success_rows');
            ImportLog::whereKey($this->importLogId)->increment('processed_rows');
        }

        return $user;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                if ($this->importLogId) {
                    $log = ImportLog::find($this->importLogId);
                    if ($log) {
                        $log->update([
                            'status' => 'completed',
                            'finished_at' => now(),
                        ]);
                        \App\Events\ImportCompleted::dispatch($log);
                    }
                }
            },
            ImportFailed::class => function (ImportFailed $event) {
                if ($this->importLogId) {
                    $log = ImportLog::find($this->importLogId);
                    if ($log) {
                        $log->update([
                            'status' => 'failed',
                            'finished_at' => now(),
                            'error_message' => $event->getException()->getMessage(),
                        ]);
                        \App\Events\ImportCompleted::dispatch($log);
                    }
                }
            },
        ];
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role'  => ['nullable', 'string', Rule::in(['customer', 'admin'])],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
