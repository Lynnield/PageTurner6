<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'import_type',
        'original_filename',
        'file_disk',
        'stored_path',
        'mode',
        'status',
        'total_rows',
        'processed_rows',
        'success_rows',
        'failed_rows',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function failures(): HasMany
    {
        return $this->hasMany(ImportLogFailure::class);
    }
}

